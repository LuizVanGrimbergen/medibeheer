<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\DailyCheckin;
use App\Models\Patient;
use App\Support\InertiaPagination;
use Carbon\CarbonImmutable;

final class FamilyWellbeingScreenService
{
    public function buildProps(string $calendarMonth, Patient $patient): array
    {
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
                $paginator->getCollection()
                    ->map(fn (DailyCheckin $checkin): array => $checkin->toDashboardPayload())
                    ->all(),
            ),
        ];
    }

    public function emptyProps(string $calendarMonth): array
    {
        return [
            'wellbeing_calendar_month' => $calendarMonth,
            'wellbeing_calendar_checkins' => [],
            'wellbeing_checkins' => InertiaPagination::empty(),
        ];
    }
}
