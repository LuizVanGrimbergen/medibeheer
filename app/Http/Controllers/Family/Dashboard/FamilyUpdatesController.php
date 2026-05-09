<?php

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Services\FamilyWellbeingUpdatesScreenService;
use App\Support\FamilyDashboardState;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyUpdatesController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(
        Request $request,
        FamilyWellbeingUpdatesScreenService $screen,
    ): Response {
        $family = $this->authorizeFamilyProfile($request);

        $activePatientId = FamilyDashboardState::activePatientId($request);

        if ($activePatientId === null) {
            return Inertia::render(
                'Family/Updates',
                $screen->emptyProps($request),
            );
        }

        $patient = $family->patients()->whereKey($activePatientId)->first();

        if ($patient === null) {
            return Inertia::render(
                'Family/Updates',
                $screen->emptyProps($request),
            );
        }

        $this->authorize('view', $patient);

        return Inertia::render(
            'Family/Updates',
            $screen->buildProps($request, $patient),
        );
    }
}
