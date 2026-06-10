<?php

namespace App\Http\Controllers\Patient\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Services\Patient\PatientMedicationRegisterService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientInventoryController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly PatientMedicationRegisterService $patientMedicationRegisterService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $patient = $this->authorizePatientProfile($request);

        return Inertia::render('Patient/Inventory/Index', [
            'medications' => Inertia::defer(
                fn (): array => $this->patientMedicationRegisterService
                    ->buildInventoryProps($patient)['medications'],
            ),
        ]);
    }
}
