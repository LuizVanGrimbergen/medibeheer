<?php

declare(strict_types=1);

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Services\Family\FamilyAcceptedTransportAppointmentsOverviewService;
use App\Services\Family\FamilyExpiringPrescriptionPatientsOverviewService;
use App\Services\Family\FamilyLowStockPatientsOverviewService;
use App\Services\Family\FamilyPendingTransportAppointmentsOverviewService;
use App\Services\Family\FamilyUpdatesScreenService;
use App\Support\FamilyDashboardState;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyOverviewController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __construct(
        private readonly FamilyLowStockPatientsOverviewService $lowStockPatientsOverviewService,
        private readonly FamilyExpiringPrescriptionPatientsOverviewService $expiringPrescriptionPatientsOverviewService,
        private readonly FamilyUpdatesScreenService $updatesScreenService,
        private readonly FamilyPendingTransportAppointmentsOverviewService $pendingTransportAppointmentsOverviewService,
        private readonly FamilyAcceptedTransportAppointmentsOverviewService $acceptedTransportAppointmentsOverviewService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $family = $this->authorizeFamilyProfile($request);

        $activePatient = FamilyDashboardState::activePatient($request);

        $updatesProps = $activePatient !== null
            ? $this->updatesScreenService->buildProps($activePatient)
            : $this->updatesScreenService->emptyProps();

        return Inertia::render('Family/Overview', [
            'low_stock_patients' => $this->lowStockPatientsOverviewService->forFamily($family),
            'expiring_prescription_patients' => $this->expiringPrescriptionPatientsOverviewService->forFamily($family),
            'updates_checkins' => $updatesProps['updates_checkins'],
            'updates_medication_intakes' => $updatesProps['updates_medication_intakes'],
            'pending_transport_appointments' => $this->pendingTransportAppointmentsOverviewService->forFamily($family),
            'accepted_transport_appointments' => $this->acceptedTransportAppointmentsOverviewService->forFamily($family),
        ]);
    }
}
