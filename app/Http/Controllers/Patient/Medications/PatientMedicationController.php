<?php

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Medications\StoreMedicationRequest;
use App\Http\Requests\Patient\Medications\UpdateMedicationRequest;
use App\Models\Medication;
use App\Services\Patient\PatientMedicationsScreenService;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PatientMedicationController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly PatientMedicationsScreenService $patientMedicationsScreenService,
    ) {}

    public function index(Request $request): Response
    {
        $patient = $this->authorizePatientProfile($request);

        return Inertia::render(
            'Patient/Medications',
            [
                ...$this->patientMedicationsScreenService->buildProps($patient),
                'can_create_medication' => $request->user()?->can('create', Medication::class) ?? false,
            ],
        );
    }

    public function store(StoreMedicationRequest $request): RedirectResponse
    {
        $patient = $this->authorizePatientProfile($request);

        $validated = $request->validated();
        $normalizedSchedule = MedicationScheduleIntakeWeekdays::normalizeNestedSchedule($validated['schedule']);
        $intakeWeekdays = $normalizedSchedule['intake_weekdays'];
        $scheduleAttributes = Arr::except($normalizedSchedule, ['intake_weekdays']);
        $stockCurrent = $validated['current_stock'] ?? null;

        $medicationAttributes = Arr::except($validated, [
            'schedule',
            'current_stock',
        ]);

        DB::transaction(function () use (
            $patient,
            $medicationAttributes,
            $scheduleAttributes,
            $intakeWeekdays,
            $stockCurrent,
        ): void {
            $medication = $patient->medications()->create([
                ...$medicationAttributes,
                'family_id' => $patient->defaultMedicationFamilyId(),
            ]);

            $createdSchedule = $medication->schedules()->create([
                ...$scheduleAttributes,
                'patient_id' => $medication->patient_id,
                'family_id' => $medication->family_id,
            ]);
            $createdSchedule->syncIntakeWeekdays($intakeWeekdays);

            $medication->stocks()->create([
                'current_stock' => $stockCurrent,
                'patient_id' => $medication->patient_id,
                'family_id' => $medication->family_id,
            ]);
        });

        return redirect()->route('patient.medications');
    }

    public function update(UpdateMedicationRequest $request, Medication $medication): RedirectResponse
    {
        $patient = $this->authorizePatientProfile($request);

        $validated = $request->validated();

        $medicationPayload = Arr::only($validated, [
            'name',
            'dose',
            'dose_unit',
            'type_medication',
            'strength',
            'note',
            'stock_pieces_per_package',
        ]);

        DB::transaction(function () use ($medication, $validated, $medicationPayload, $patient): void {
            if ($medicationPayload !== []) {
                $medication->fill([
                    ...$medicationPayload,
                    'family_id' => $patient->defaultMedicationFamilyId(),
                ])->save();

                $medication->stocks()->update([
                    'family_id' => $medication->family_id,
                    'patient_id' => $medication->patient_id,
                ]);
            }

            if (isset($validated['schedule'])) {
                $normalizedSchedule = MedicationScheduleIntakeWeekdays::normalizeNestedSchedule(
                    $validated['schedule'],
                );
                $intakeWeekdays = $normalizedSchedule['intake_weekdays'];
                $scheduleAttrs = Arr::only($normalizedSchedule, [
                    'meal_timing',
                    'intake_frequency',
                    'times_per_day',
                    'dose_quantity',
                    'dose_time',
                    'snooze_time',
                    'start_date',
                    'end_date',
                ]);

                $scheduleModel = $medication->schedules()->first();

                if ($scheduleModel !== null) {
                    $scheduleModel->fill($scheduleAttrs)->save();
                    $scheduleModel->syncIntakeWeekdays($intakeWeekdays);
                }
            }

            if (array_key_exists('current_stock', $validated)) {
                $stock = $medication->stocks()->first();

                if ($stock !== null) {
                    $stock->fill([
                        'current_stock' => $validated['current_stock'],
                        'family_id' => $medication->family_id,
                        'patient_id' => $medication->patient_id,
                    ])->save();
                }
            }
        });

        return redirect()->route('patient.medications');
    }

    public function destroy(Request $request, Medication $medication): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('delete', $medication);

        DB::transaction(function () use ($medication): void {
            $medication->schedules()->delete();
            $medication->stocks()->delete();
            Medication::query()->whereKey($medication->getKey())->delete();
        });

        return redirect()->route('patient.medications');
    }
}
