<?php

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Medications\StoreMedicationRequest;
use App\Http\Requests\Patient\Medications\UpdateMedicationRequest;
use App\Models\Medication;
use App\Services\Patient\PatientMedicationsScreenService;
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

        $this->authorize('create', Medication::class);

        $validated = $request->validated();
        $schedule = $validated['schedule'];
        $stockCurrent = $validated['current_stock'] ?? null;
        $stockLow = $validated['low_stock'] ?? null;

        $medicationAttributes = Arr::except($validated, [
            'schedule',
            'current_stock',
            'low_stock',
        ]);

        DB::transaction(function () use (
            $patient,
            $medicationAttributes,
            $schedule,
            $stockCurrent,
            $stockLow,
        ): void {
            $medication = $patient->medications()->create([
                ...$medicationAttributes,
                'family_id' => $patient->defaultMedicationFamilyId(),
            ]);

            $medication->schedules()->create([
                ...$schedule,
                'patient_id' => $medication->patient_id,
                'family_id' => $medication->family_id,
            ]);

            $medication->stocks()->create([
                'current_stock' => $stockCurrent,
                'low_stock' => $stockLow,
                'patient_id' => $medication->patient_id,
                'family_id' => $medication->family_id,
            ]);
        });

        return redirect()->route('patient.medications');
    }

    public function update(UpdateMedicationRequest $request, Medication $medication): RedirectResponse
    {
        $patient = $this->authorizePatientProfile($request);

        $this->authorize('update', $medication);

        $validated = $request->validated();

        $medicationPayload = Arr::only($validated, [
            'name',
            'dose',
            'dose_unit',
            'type_medication',
            'strength',
            'note',
        ]);

        DB::transaction(function () use ($medication, $validated, $medicationPayload, $patient): void {
            if ($medicationPayload !== []) {
                $medication->update([
                    ...$medicationPayload,
                    'family_id' => $patient->defaultMedicationFamilyId(),
                ]);

                $medication->stocks()->update([
                    'family_id' => $medication->family_id,
                    'patient_id' => $medication->patient_id,
                ]);
            }

            if (isset($validated['schedule'])) {
                $scheduleAttrs = Arr::only($validated['schedule'], [
                    'meal_timing',
                    'intake_frequency',
                    'intake_weekdays',
                    'times_per_day',
                    'dose_quantity',
                    'dose_time',
                    'start_date',
                    'end_date',
                ]);
                $medication->schedules()->first()?->update($scheduleAttrs);
            }

            if (array_key_exists('current_stock', $validated) && array_key_exists('low_stock', $validated)) {
                $medication->stocks()->first()?->update([
                    ...Arr::only($validated, ['current_stock', 'low_stock']),
                    'family_id' => $medication->family_id,
                    'patient_id' => $medication->patient_id,
                ]);
            }
        });

        return redirect()->route('patient.medications');
    }

    public function destroy(Request $request, Medication $medication): RedirectResponse
    {
        $this->authorizePatientProfile($request);

        $this->authorize('delete', $medication);

        Medication::destroy($medication->getKey());

        return redirect()->route('patient.medications');
    }
}
