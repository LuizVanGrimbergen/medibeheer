<?php

declare(strict_types=1);

namespace App\Support\Medications;

use App\Enums\MedicationIntakeFrequency;
use App\Models\MedicationSchedule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class MedicationScheduleOccursOnDate
{
    public function isActiveOn(MedicationSchedule $schedule, CarbonInterface $date): bool
    {
        $dateString = $date->toDateString();

        $startDate = $schedule->start_date;
        if ($startDate instanceof CarbonInterface && $dateString < $startDate->toDateString()) {
            return false;
        }

        $endDate = $schedule->end_date;
        if ($endDate instanceof CarbonInterface && $dateString > $endDate->toDateString()) {
            return false;
        }

        return true;
    }

    public function isIntakeDueOn(MedicationSchedule $schedule, CarbonInterface $date): bool
    {
        if (! $this->isActiveOn($schedule, $date)) {
            return false;
        }

        $frequency = (string) $schedule->intake_frequency;
        $everyNDays = MedicationIntakeFrequency::parseEveryNDays($frequency);

        return match (true) {
            $frequency === MedicationIntakeFrequency::DAILY => true,
            $frequency === MedicationIntakeFrequency::WEEKDAYS => $this->isWeekdayIntakeDue($schedule, $date),
            $everyNDays !== null => $this->isEveryNDaysIntakeDue($schedule, $date, $everyNDays),
            default => false,
        };
    }

    public function sortedDoseTimes(MedicationSchedule $schedule): array
    {
        return MedicationScheduleDoseTimes::sortedTimes(
            (string) $schedule->dose_time,
            $schedule->snooze_time,
        );
    }

    public function hasScheduledDoseOn(
        MedicationSchedule $schedule,
        string $doseTime,
        CarbonInterface $date,
    ): bool {
        if (! $this->isIntakeDueOn($schedule, $date)) {
            return false;
        }

        return in_array(trim($doseTime), $this->sortedDoseTimes($schedule), true);
    }

    private function isWeekdayIntakeDue(MedicationSchedule $schedule, CarbonInterface $date): bool
    {
        $weekdays = $schedule->intake_weekdays;

        if (! is_array($weekdays) || $weekdays === []) {
            return false;
        }

        return in_array($date->dayOfWeekIso, $weekdays, true);
    }

    private function isEveryNDaysIntakeDue(
        MedicationSchedule $schedule,
        CarbonInterface $date,
        int $everyNDays,
    ): bool {
        $anchor = $schedule->start_date ?? $schedule->created_at;

        if ($anchor === null) {
            return true;
        }

        $anchorDay = CarbonImmutable::parse($anchor)->startOfDay();
        $targetDay = CarbonImmutable::parse($date)->startOfDay();

        if ($targetDay->lt($anchorDay)) {
            return false;
        }

        return $anchorDay->diffInDays($targetDay) % $everyNDays === 0;
    }
}
