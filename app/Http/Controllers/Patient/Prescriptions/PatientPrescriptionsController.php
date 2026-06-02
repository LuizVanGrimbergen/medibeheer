<?php

namespace App\Http\Controllers\Patient\Prescriptions;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Services\Patient\PatientMedicationsScreenService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientPrescriptionsController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly PatientMedicationsScreenService $patientMedicationsScreenService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $patient = $this->authorizePatientProfile($request);

        return Inertia::render(
            'Patient/Prescriptions',
            $this->patientMedicationsScreenService->buildPrescriptionsProps($patient),
        );
    }
}
