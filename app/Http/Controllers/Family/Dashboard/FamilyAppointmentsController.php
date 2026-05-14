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

        return Inertia::render(
            'Family/Appointments',
            $screen->buildProps(
                $request,
                $family,
                FamilyDashboardState::activePatientId($request),
            ),
        );
    }
}
