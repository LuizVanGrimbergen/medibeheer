<?php

declare(strict_types=1);

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Services\Family\FamilyWellbeingScreenService;
use App\Support\CalendarMonth;
use App\Support\FamilyDashboardState;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class FamilyWellbeingController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(
        Request $request,
        FamilyWellbeingScreenService $screen,
    ): Response {
        $this->authorizeFamilyProfile($request);

        $calendarMonth = CalendarMonth::fromRequest($request);
        $patient = FamilyDashboardState::activePatient($request);

        if ($patient === null) {
            return Inertia::render(
                'Family/Wellbeing',
                $screen->emptyProps($calendarMonth),
            );
        }

        $this->authorize('view', $patient);

        return Inertia::render(
            'Family/Wellbeing',
            $screen->buildProps($calendarMonth, $patient),
        );
    }
}
