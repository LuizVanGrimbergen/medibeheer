<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Models\Patient;
use App\Support\Medications\PatientRecentPushMedicationMarkStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowPatientPushMedicationMarkSuccessController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        Request $request,
        PatientRecentPushMedicationMarkStore $recentPushMarkStore,
    ): Response|RedirectResponse {
        $patient = $this->authorizePatientProfile($request);

        $medicationName = $this->resolveMedicationName($patient, $request, $recentPushMarkStore);

        if ($medicationName === null) {
            return redirect()->route('patient.dashboard');
        }

        return Inertia::render('Patient/MedicationPushMarkSuccess', [
            'medication_name' => $medicationName,
        ]);
    }

    private function resolveMedicationName(
        Patient $patient,
        Request $request,
        PatientRecentPushMedicationMarkStore $recentPushMarkStore,
    ): ?string {
        $fromStore = $recentPushMarkStore->peek($patient->id);

        if ($fromStore !== null) {
            return $fromStore;
        }

        $fromQuery = trim($request->string('medication')->toString());

        if ($fromQuery === '') {
            return null;
        }

        if (! $this->patientHasMedicationNamed($patient, $fromQuery)) {
            return null;
        }

        return $fromQuery;
    }

    private function patientHasMedicationNamed(Patient $patient, string $name): bool
    {
        return $patient->medications
            ->contains(fn ($medication): bool => $medication->name === $name);
    }
}
