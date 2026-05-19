<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\DailyCheckin;
use App\Models\Patient;
use Carbon\CarbonImmutable;

final class FamilyDailyCheckinListService
{
    public function withinDaysForPatient(Patient $patient, int $days): array
    {
        $today = CarbonImmutable::today();
        $from = $today->subDays($days - 1);

        return DailyCheckin::query()
            ->whereBelongsTo($patient)
            ->whereDate('checkin_date', '>=', $from->toDateString())
            ->whereDate('checkin_date', '<=', $today->toDateString())
            ->with('selectedSymptoms')
            ->orderByDesc('checkin_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (DailyCheckin $checkin): array => $checkin->toDashboardPayload())
            ->values()
            ->all();
    }
}
