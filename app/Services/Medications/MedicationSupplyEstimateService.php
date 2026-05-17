<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Enums\MedicationIntakeFrequency;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use App\Support\Medications\MedicationStockNumericParser;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class MedicationSupplyEstimateService
{
    public function __construct(
        private readonly MedicationStockNumericParser $stockNumericParser,
        private readonly MedicationScheduleOccursOnDate $scheduleOccursOnDate,
    ) {}

    public function estimate(Medication $medication, ?CarbonInterface $asOfDate = null): array
    {
        $days = $this->resolveEstimatedSupplyDays($medication, $asOfDate);

        return [
            'days' => $days,
            'quality' => $days === null ? 'unknown' : 'approx',
        ];
    }

    private function resolveEstimatedSupplyDays(
        Medication $medication,
        ?CarbonInterface $asOfDate,
    ): ?int {
        $stock = $medication->stocks->first();
        $current = $stock === null
            ? null
            : $this->stockNumericParser->parse(
                (string) $stock->current_stock,
                $medication->dose_unit,
            );

        if ($current === null || $medication->schedules->isEmpty()) {
            return null;
        }

        $medication->loadMissing('schedules.weekdays');

        $referenceDate = $asOfDate === null
            ? CarbonImmutable::today()
            : CarbonImmutable::parse($asOfDate)->startOfDay();
        $dailyTotal = 0.0;

        foreach ($medication->schedules as $schedule) {
            if (! $this->scheduleOccursOnDate->isActiveOn($schedule, $referenceDate)) {
                continue;
            }

            $daily = $this->consumptionUnitsPerDay($schedule);

            if ($daily !== null) {
                $dailyTotal += $daily;
            }
        }

        if ($dailyTotal <= 0.0) {
            return null;
        }

        return (int) floor($current / $dailyTotal);
    }

    private function consumptionUnitsPerDay(MedicationSchedule $schedule): ?float
    {
        $dose = $this->parsePositiveFloat((string) $schedule->dose_quantity);
        $times = $this->parsePositiveFloat((string) $schedule->times_per_day);
        $intakeDayFraction = $this->intakeDayFraction($schedule);

        if ($dose === null || $times === null || $intakeDayFraction === null) {
            return null;
        }

        return $dose * $times * $intakeDayFraction;
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
