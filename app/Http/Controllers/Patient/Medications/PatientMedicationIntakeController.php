<?php

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Medications\StoreMedicationIntakeRequest;
use App\Models\MedicationSchedule;
use App\Services\Medications\RecordPatientMedicationIntakeService;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\PatientRecentPushMedicationMarkStore;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;

class PatientMedicationIntakeController extends Controller
{
    use AuthorizesPatientProfile;

    public function store(
        StoreMedicationIntakeRequest $request,
        RecordPatientMedicationIntakeService $recordIntake,
        PatientRecentPushMedicationMarkStore $recentPushMarkStore,
    ): RedirectResponse {
        $patient = $this->authorizePatientProfile($request);

        $validated = $request->validated();

        $schedule = MedicationSchedule::query()
            ->whereKey($validated['medication_schedule_id'])
            ->with('medication')
            ->firstOrFail();

        $this->authorize('view', $schedule->medication);

        $doseTime = $request->filled('dose_time')
            ? trim((string) $validated['dose_time'])
            : '';

        $takenAt = isset($validated['taken_at'])
            ? CarbonImmutable::parse((string) $validated['taken_at'], MedicationIntakeClock::TIMEZONE)
            : null;

        $allowOutsideWindow = $request->boolean('late_intake') || $request->filled('taken_at');

        $recordIntake->recordTodayForSchedule(
            $patient,
            $schedule,
            $doseTime,
            allowOutsideIntakeWindow: $allowOutsideWindow,
            takenAt: $takenAt,
        );

        $recentPushMarkStore->remember(
            $patient->id,
            (string) $schedule->medication->name,
        );

        return redirect()->route('patient.medication-push-mark.success');
    }
}
