<?php

declare(strict_types=1);

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Services\FamilyWellbeingScreenService;
use App\Support\FamilyDashboardState;
use Carbon\CarbonImmutable;
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

        $calendarMonth = self::normalizedCalendarMonth($request);
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

    private static function normalizedCalendarMonth(Request $request): string
    {
        $raw = $request->query('calendar_month');

        if (! is_string($raw) || ! preg_match('/^\d{4}-(?<m>\d{2})$/', $raw, $matches)) {
            return CarbonImmutable::now()->format('Y-m');
        }

        $monthNum = (int) $matches['m'];

        if ($monthNum < 1 || $monthNum > 12) {
            return CarbonImmutable::now()->format('Y-m');
        }

        return $raw;
    }
}
