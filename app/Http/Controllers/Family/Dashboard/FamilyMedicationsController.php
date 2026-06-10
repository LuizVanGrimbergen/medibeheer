<?php

declare(strict_types=1);

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Services\Family\FamilyMedicationsScreenService;
use App\Support\CalendarMonth;
use App\Support\FamilyDashboardState;
use App\Support\InertiaPagination;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class FamilyMedicationsController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(
        Request $request,
        FamilyMedicationsScreenService $screen,
    ): Response {
        $this->authorizeFamilyProfile($request);

        $calendarMonth = CalendarMonth::fromRequest($request);
        $patient = FamilyDashboardState::activePatient($request);

        if ($patient === null) {
            return Inertia::render('Family/Medications/Index', [
                'medication_calendar_month' => $calendarMonth,
                'medications' => Inertia::defer(fn (): array => InertiaPagination::empty()),
                'medication_calendar_days' => Inertia::defer(fn (): array => []),
                'medication_calendar_slots' => Inertia::defer(fn (): array => []),
            ]);
        }

        $this->authorize('view', $patient);

        $calendarData = null;

        return Inertia::render('Family/Medications/Index', [
            'medication_calendar_month' => $calendarMonth,
            'medications' => Inertia::defer(
                fn (): array => $screen->paginatedMedicationsFor($request, $patient)['medications'],
            ),
            'medication_calendar_days' => Inertia::defer(
                function () use ($screen, $patient, $calendarMonth, &$calendarData): array {
                    $calendarData ??= $screen->calendarDataFor($patient, $calendarMonth);

                    return $calendarData['medication_calendar_days'];
                },
            ),
            'medication_calendar_slots' => Inertia::defer(
                function () use ($screen, $patient, $calendarMonth, &$calendarData): array {
                    $calendarData ??= $screen->calendarDataFor($patient, $calendarMonth);

                    return $calendarData['medication_calendar_slots'];
                },
            ),
        ]);
    }
}
