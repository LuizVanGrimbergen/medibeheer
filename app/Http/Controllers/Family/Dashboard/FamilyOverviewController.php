<?php

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Services\Family\FamilyAcceptedTransportAppointmentsOverviewService;
use App\Services\Family\FamilyLowStockPatientsOverviewService;
use App\Services\Family\FamilyPendingTransportAppointmentsOverviewService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyOverviewController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __construct(
        private readonly FamilyLowStockPatientsOverviewService $lowStockPatientsOverviewService,
        private readonly FamilyPendingTransportAppointmentsOverviewService $pendingTransportAppointmentsOverviewService,
        private readonly FamilyAcceptedTransportAppointmentsOverviewService $acceptedTransportAppointmentsOverviewService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $family = $this->authorizeFamilyProfile($request);

        return Inertia::render('Family/Overview', [
            'low_stock_patients' => $this->lowStockPatientsOverviewService->forFamily($family),
            'pending_transport_appointments' => $this->pendingTransportAppointmentsOverviewService->forFamily($family),
            'accepted_transport_appointments' => $this->acceptedTransportAppointmentsOverviewService->forFamily($family),
        ]);
    }
}
