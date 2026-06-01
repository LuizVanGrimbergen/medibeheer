<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Models\Medication;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use App\Support\Medications\MedicationStockNumericParser;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class MedicationSupplyEstimateService
{
    public function __construct(
        private readonly MedicationStockNumericParser $stockNumericParser,
        private readonly MedicationScheduleOccursOnDate $scheduleOccursOnDate,
        private readonly MedicationScheduleDailyConsumption $scheduleDailyConsumption,
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

            $daily = $this->scheduleDailyConsumption->averagedUnitsPerCalendarDay($schedule);

            if ($daily !== null) {
                $dailyTotal += $daily;
            }
        }

        if ($dailyTotal <= 0.0) {
            return null;
        }

        return (int) floor($current / $dailyTotal);
    }
}
