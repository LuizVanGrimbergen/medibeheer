<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\DailyCheckin;
use App\Models\Patient;
use App\Support\InertiaPagination;
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
            ->map(fn (DailyCheckin $checkin): array => $this->familyDashboardPayload($checkin, $patient))
            ->values()
            ->all();
    }

    public function paginatedForPatient(Patient $patient): array
    {
        $paginator = DailyCheckin::query()
            ->whereBelongsTo($patient)
            ->with('selectedSymptoms')
            ->orderByDesc('checkin_date')
            ->orderByDesc('id')
            ->paginate(InertiaPagination::PER_PAGE)
            ->withQueryString();

        return InertiaPagination::payload(
            $paginator,
            $paginator->getCollection()
                ->map(fn (DailyCheckin $checkin): array => $this->familyDashboardPayload($checkin, $patient))
                ->all(),
        );
    }

    private function familyDashboardPayload(DailyCheckin $checkin, Patient $patient): array
    {
        $patient->loadMissing('user');

        return $checkin->toFamilyDashboardPayload(
            (string) ($patient->user?->name ?? ''),
        );
    }

    public function empty(): array
    {
        return InertiaPagination::empty();
    }
}
