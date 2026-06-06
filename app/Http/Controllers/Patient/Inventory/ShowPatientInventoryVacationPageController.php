<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Services\Medications\PatientCriticalPrescriptionsQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ShowPatientInventoryVacationPageController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly PatientCriticalPrescriptionsQuery $criticalPrescriptionsQuery,
    ) {}

    public function __invoke(Request $request): Response
    {
        $patient = $this->authorizePatientProfile($request);

        return Inertia::render('Patient/Inventory/Vacation', [
            'starts_on' => '',
            'ends_on' => '',
            'result' => null,
            'expiring_prescriptions' => $this->criticalPrescriptionsQuery
                ->forPatientNavAlerts($patient),
        ]);
    }
}
