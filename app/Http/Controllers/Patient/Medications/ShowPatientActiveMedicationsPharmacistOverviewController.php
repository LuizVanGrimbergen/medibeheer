<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Services\Patient\PatientMedicationsScreenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowPatientActiveMedicationsPharmacistOverviewController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly PatientMedicationsScreenService $patientMedicationsScreenService,
    ) {}

    public function __invoke(Request $request): Response|RedirectResponse
    {
        $patient = $this->authorizePatientProfile($request);

        $medicationNames = $this->patientMedicationsScreenService->activeMedicationNamesFor($patient);

        if ($medicationNames === []) {
            return redirect()->route('patient.medications');
        }

        return Inertia::render('Patient/Medications/PharmacistOverview', [
            'medication_names' => $medicationNames,
        ]);
    }
}
