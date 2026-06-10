<?php

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Services\Family\FamilyAppointmentsScreenService;
use App\Support\FamilyDashboardState;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyAppointmentsController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(Request $request, FamilyAppointmentsScreenService $screen): Response
    {
        $family = $this->authorizeFamilyProfile($request);
        $activePatientId = FamilyDashboardState::activePatientId($request);
        $shellProps = $screen->buildProps($request, $family, $activePatientId);

        return Inertia::render('Family/Appointments/Index', [
            'appointment_view' => $shellProps['appointment_view'],
            'appointment_tab_totals' => $shellProps['appointment_tab_totals'],
            'appointments' => Inertia::defer(
                fn (): array => $screen->paginatedAppointmentsFor(
                    $request,
                    $family,
                    $activePatientId,
                ),
            ),
        ]);
    }
}
