<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DailyCheckin;
use App\Models\Patient;
use App\Support\InertiaPagination;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

final class FamilyWellbeingUpdatesScreenService
{
    public function buildProps(Request $request, Patient $patient): array
    {
        $calendarMonth = CarbonImmutable::now()->format('Y-m');
        $calendarCheckinsPayload = [];

        if ($request->routeIs('family.wellbeing')) {
            $calendarMonth = self::normalizedCalendarMonth($request);

            $monthStart = CarbonImmutable::createFromFormat('Y-m', $calendarMonth)->startOfMonth();
            $monthEnd = $monthStart->endOfMonth();

            $calendarCheckinsPayload = DailyCheckin::query()
                ->whereBelongsTo($patient)
                ->whereBetween('checkin_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->with('selectedSymptoms')
                ->orderBy('checkin_date')
                ->orderBy('id')
                ->get()
                ->map(fn (DailyCheckin $checkin): array => $checkin->toDashboardPayload())
                ->values()
                ->all();
        }

        $paginator = DailyCheckin::query()
            ->whereBelongsTo($patient)
            ->with('selectedSymptoms')
            ->orderByDesc('checkin_date')
            ->orderByDesc('id')
            ->paginate(InertiaPagination::PER_PAGE)
            ->withQueryString();

        return [
            'wellbeing_calendar_month' => $calendarMonth,
            'wellbeing_calendar_checkins' => $calendarCheckinsPayload,
            'wellbeing_checkins' => InertiaPagination::payload(
                $paginator,
                collect($paginator->items())
                    ->map(fn (DailyCheckin $checkin) => $checkin->toDashboardPayload())
                    ->all(),
            ),
        ];
    }

    public function emptyProps(Request $request): array
    {
        $calendarMonth = $request->routeIs('family.wellbeing')
            ? self::normalizedCalendarMonth($request)
            : CarbonImmutable::now()->format('Y-m');

        return [
            'wellbeing_calendar_month' => $calendarMonth,
            'wellbeing_calendar_checkins' => [],
            'wellbeing_checkins' => InertiaPagination::empty(),
        ];
    }

    private static function normalizedCalendarMonth(Request $request): string
    {
        $raw = $request->query('calendar_month');

        if (! is_string($raw) || ! preg_match('/^(?<y>\d{4})-(?<m>\d{2})$/', $raw, $matches)) {
            return CarbonImmutable::now()->format('Y-m');
        }

        $monthNum = (int) $matches['m'];

        if ($monthNum < 1 || $monthNum > 12) {
            return CarbonImmutable::now()->format('Y-m');
        }

        return $raw;
    }
}
