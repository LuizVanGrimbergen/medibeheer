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

        $updatesProps = null;

        return Inertia::render('Family/Overview/Index', [
            'low_stock_patients' => Inertia::defer(
                fn (): array => $this->lowStockPatientsOverviewService->forFamily($family),
            ),
            'expiring_prescription_patients' => Inertia::defer(
                fn (): array => $this->expiringPrescriptionPatientsOverviewService->forFamily($family),
            ),
            'updates_checkins' => Inertia::defer(
                function () use ($activePatient, &$updatesProps): array {
                    $updatesProps ??= $activePatient !== null
                        ? $this->updatesScreenService->buildProps($activePatient)
                        : $this->updatesScreenService->emptyProps();

                    return $updatesProps['updates_checkins'];
                },
            ),
            'updates_medication_intakes' => Inertia::defer(
                function () use ($activePatient, &$updatesProps): array {
                    $updatesProps ??= $activePatient !== null
                        ? $this->updatesScreenService->buildProps($activePatient)
                        : $this->updatesScreenService->emptyProps();

                    return $updatesProps['updates_medication_intakes'];
                },
            ),
            'pending_transport_appointments' => Inertia::defer(
                fn (): array => $this->pendingTransportAppointmentsOverviewService->forFamily($family),
            ),
            'accepted_transport_appointments' => Inertia::defer(
                fn (): array => $this->acceptedTransportAppointmentsOverviewService->forFamily($family),
            ),
        ]);
    }
}
