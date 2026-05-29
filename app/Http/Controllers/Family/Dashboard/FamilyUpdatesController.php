<?php

declare(strict_types=1);

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Services\Family\FamilyUpdatesScreenService;
use App\Support\FamilyDashboardState;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class FamilyUpdatesController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(
        Request $request,
        FamilyUpdatesScreenService $screen,
    ): Response {
        $this->authorizeFamilyProfile($request);

        $patient = FamilyDashboardState::activePatient($request);

        if ($patient === null) {
            return Inertia::render('Family/Updates', $screen->emptyProps($request));
        }

        $this->authorize('view', $patient);

        return Inertia::render('Family/Updates', $screen->buildProps($request, $patient));
    }
}
