<?php

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Medications\StoreMedicationIntakeRequest;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;

class PatientMedicationIntakeController extends Controller
{
    use AuthorizesPatientProfile;

    public function store(StoreMedicationIntakeRequest $request): RedirectResponse
    {
        $patient = $this->authorizePatientProfile($request);

        $validated = $request->validated();
        $today = CarbonImmutable::today();

        $schedule = MedicationSchedule::query()
            ->whereKey($validated['medication_schedule_id'])
            ->firstOrFail();

        $this->authorize('view', $schedule->medication);

        $doseTime = $request->filled('dose_time')
            ? trim((string) $validated['dose_time'])
            : '';

        $intake = MedicationIntake::query()->firstOrNew([
            'medication_schedule_id' => $schedule->id,
            'intake_date' => $today,
            'dose_time' => $doseTime,
        ]);

        $intake->fill([
            'patient_id' => $patient->id,
            'medication_id' => $schedule->medication_id,
            'taken_at' => now(),
        ]);
        $intake->save();

        return redirect()->route('patient.dashboard');
    }
}
