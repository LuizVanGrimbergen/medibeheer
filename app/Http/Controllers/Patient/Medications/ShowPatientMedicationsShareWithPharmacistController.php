<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Medications;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Services\Patient\PatientMedicationRegisterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowPatientMedicationsShareWithPharmacistController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly PatientMedicationRegisterService $patientMedicationRegisterService,
    ) {}

    public function __invoke(Request $request): Response|RedirectResponse
    {
        $patient = $this->authorizePatientProfile($request);

        if ($this->patientMedicationRegisterService->activeMedicationNamesFor($patient) === []) {
            return redirect()->route('patient.medications');
        }

        return Inertia::render('Patient/Medications/ShareWithPharmacist', [
            'medication_names' => Inertia::defer(
                fn (): array => $this->patientMedicationRegisterService->activeMedicationNamesFor($patient),
            ),
        ]);
    }
}
