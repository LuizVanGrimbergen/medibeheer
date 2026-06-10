<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\DailyCheckin;
use App\Models\Patient;
use Carbon\CarbonImmutable;

final class FamilyWellbeingScreenService
{
    public function __construct(
        private FamilyDailyCheckinListService $checkinList,
    ) {}

    public function buildProps(string $calendarMonth, Patient $patient): array
    {
        return [
            'wellbeing_calendar_month' => $calendarMonth,
            ...$this->checkinDataFor($calendarMonth, $patient),
        ];
    }

    /** @return array{wellbeing_calendar_checkins: list<array<string, mixed>>, wellbeing_checkins: array{data: list<array<string, mixed>>, meta: array<string, mixed>}} */
    public function checkinDataFor(string $calendarMonth, Patient $patient): array
    {
        $monthStart = CarbonImmutable::createFromFormat('Y-m', $calendarMonth)->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        $patient->loadMissing('user');
        $patientName = (string) ($patient->user?->name ?? '');

        $calendarCheckinsPayload = DailyCheckin::query()
            ->whereBelongsTo($patient)
            ->whereBetween('checkin_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with('selectedSymptoms')
            ->orderBy('checkin_date')
            ->orderBy('id')
            ->get()
            ->map(fn (DailyCheckin $checkin): array => $checkin->toFamilyDashboardPayload($patientName))
            ->values()
            ->all();

        return [
            'wellbeing_calendar_checkins' => $calendarCheckinsPayload,
            'wellbeing_checkins' => $this->checkinList->paginatedForPatient($patient),
        ];
    }

    public function emptyProps(string $calendarMonth): array
    {
        return [
            'wellbeing_calendar_month' => $calendarMonth,
            'wellbeing_calendar_checkins' => [],
            'wellbeing_checkins' => $this->checkinList->empty(),
        ];
    }
}
