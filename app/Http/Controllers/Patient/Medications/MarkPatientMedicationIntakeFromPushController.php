<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Models\MedicationSchedule;
use App\Services\Medications\RecordPatientMedicationIntakeService;
use App\Support\Medications\PatientRecentPushMedicationMarkStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class MarkPatientMedicationIntakeFromPushController extends Controller
{
    public function __invoke(
        Request $request,
        MedicationSchedule $medicationSchedule,
        RecordPatientMedicationIntakeService $recordIntake,
        PatientRecentPushMedicationMarkStore $recentPushMarkStore,
    ): RedirectResponse|Response {
        $medicationSchedule->loadMissing('medication.patient');

        $patient = $medicationSchedule->medication?->patient;

        if ($patient === null) {
            abort(404);
        }

        $doseTime = trim((string) $request->query('doseTime', ''));
        $medicationName = (string) $medicationSchedule->medication->name;

        $recordIntake->recordTodayForSchedule(
            $patient,
            $medicationSchedule,
            $doseTime,
            allowOutsideIntakeWindow: true,
        );

        if ($this->isServiceWorkerPushRequest($request)) {
            $recentPushMarkStore->remember($patient->id, $medicationName);

            return response()->noContent();
        }

        $recentPushMarkStore->remember($patient->id, $medicationName);

        return redirect()->route('patient.medication-push-mark.success');
    }

    private function isServiceWorkerPushRequest(Request $request): bool
    {
        return $request->isMethod('POST')
            || $request->header('X-Push-Mark') === '1';
    }
}
