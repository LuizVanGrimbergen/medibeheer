<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Enums\MedicationIntakeFrequency;
use App\Models\MedicationSchedule;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use Carbon\CarbonInterface;

final class MedicationScheduleDailyConsumption
{
    public function __construct(
        private readonly MedicationScheduleOccursOnDate $scheduleOccursOnDate,
    ) {}

    public function unitsWhenIntakeDueOn(
        MedicationSchedule $schedule,
        CarbonInterface $date,
    ): ?float {
        if (! $this->scheduleOccursOnDate->isIntakeDueOn($schedule, $date)) {
            return null;
        }

        return $this->unitsPerIntakeDay($schedule);
    }

    public function unitsPerIntakeDay(MedicationSchedule $schedule): ?float
    {
        $dose = $this->parsePositiveFloat((string) $schedule->dose_quantity);
        $times = $this->parsePositiveFloat((string) $schedule->times_per_day);

        if ($dose === null || $times === null) {
            return null;
        }

        return $dose * $times;
    }

    public function averagedUnitsPerCalendarDay(MedicationSchedule $schedule): ?float
    {
        $unitsPerIntakeDay = $this->unitsPerIntakeDay($schedule);

        if ($unitsPerIntakeDay === null) {
            return null;
        }

        $intakeDayFraction = $this->intakeDayFraction($schedule);

        if ($intakeDayFraction === null) {
            return null;
        }

        return $unitsPerIntakeDay * $intakeDayFraction;
    }

    private function intakeDayFraction(MedicationSchedule $schedule): ?float
    {
        $frequency = (string) $schedule->intake_frequency;
        $everyNDays = MedicationIntakeFrequency::parseEveryNDays($frequency);

        return match (true) {
            $frequency === MedicationIntakeFrequency::DAILY => 1.0,
            $frequency === MedicationIntakeFrequency::WEEKDAYS => $this->selectedWeekdaysFraction($schedule),
            $everyNDays !== null => 1.0 / $everyNDays,
            default => null,
        };
    }

    private function selectedWeekdaysFraction(MedicationSchedule $schedule): ?float
    {
        $selectedWeekdays = $schedule->intake_weekdays;

        if (! is_array($selectedWeekdays) || $selectedWeekdays === []) {
            return null;
        }

        return count($selectedWeekdays) / 7.0;
    }

    private function parsePositiveFloat(string $value): ?float
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $trimmed);
        $float = is_numeric($normalized) ? (float) $normalized : null;

        if ($float === null || ! is_finite($float) || $float <= 0.0) {
            return null;
        }

        return $float;
    }
}
