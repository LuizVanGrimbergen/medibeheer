<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Enums\MedicationIntakeFrequency;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Support\Medications\MedicationStockNumericParser;
use Carbon\Carbon;
use Carbon\CarbonInterface;

final class MedicationSupplyEstimateService
{
    public function __construct(
        private readonly MedicationStockNumericParser $stockNumericParser,
    ) {}

    public function estimate(Medication $medication): array
    {
        $days = $this->resolveEstimatedSupplyDays($medication);

        return [
            'days' => $days === null ? null : max(0, $days),
            'quality' => $days === null ? 'unknown' : 'approx',
        ];
    }

    private function resolveEstimatedSupplyDays(Medication $medication): ?int
    {
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

        $today = Carbon::today();
        $dailyTotal = 0.0;

        foreach ($medication->schedules as $schedule) {
            if (! $this->scheduleIsActiveOn($schedule, $today)) {
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

    private function scheduleIsActiveOn(MedicationSchedule $schedule, Carbon $today): bool
    {
        $todayDate = $today->toDateString();

        $startDate = $schedule->start_date;
        if ($startDate instanceof CarbonInterface && $todayDate < $startDate->toDateString()) {
            return false;
        }

        $endDate = $schedule->end_date;
        if ($endDate instanceof CarbonInterface && $todayDate > $endDate->toDateString()) {
            return false;
        }

        return true;
    }

    private function consumptionUnitsPerDay(MedicationSchedule $schedule): ?float
    {
        $dose = $this->parsePositiveFloat((string) $schedule->dose_quantity);
        $times = $this->parsePositiveFloat((string) $schedule->times_per_day);

        if ($dose === null || $times === null) {
            return null;
        }

        $unitsPerActiveDay = $dose * $times;
        $frequency = (string) $schedule->intake_frequency;
        $everyNDays = MedicationIntakeFrequency::parseEveryNDays($frequency);

        return match (true) {
            $frequency === MedicationIntakeFrequency::DAILY => $unitsPerActiveDay,
            $frequency === MedicationIntakeFrequency::WEEKDAYS => $this->selectedWeekdaysConsumptionPerDay(
                $schedule,
                $unitsPerActiveDay,
            ),
            $everyNDays !== null => $unitsPerActiveDay / (float) $everyNDays,
            default => null,
        };
    }

    private function selectedWeekdaysConsumptionPerDay(
        MedicationSchedule $schedule,
        float $unitsPerActiveDay,
    ): ?float {
        $selectedWeekdays = $schedule->intake_weekdays;

        if (! is_array($selectedWeekdays) || $selectedWeekdays === []) {
            return null;
        }

        return ($unitsPerActiveDay * count($selectedWeekdays)) / 7.0;
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
