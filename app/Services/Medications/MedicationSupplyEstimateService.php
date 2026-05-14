<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Enums\MedicationIntakeFrequency;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use Carbon\Carbon;

final class MedicationSupplyEstimateService
{
    public function estimate(Medication $medication): array
    {
        $stock = $medication->stocks->first();

        if ($stock === null) {
            return ['days' => null, 'quality' => 'unknown'];
        }

        $current = $this->parsePositiveFloat((string) $stock->current_stock);

        if ($current === null) {
            return ['days' => null, 'quality' => 'unknown'];
        }

        if ($medication->schedules->isEmpty()) {
            return ['days' => null, 'quality' => 'unknown'];
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
            return ['days' => null, 'quality' => 'unknown'];
        }

        $days = (int) round($current / $dailyTotal);

        if ($days < 0) {
            $days = 0;
        }

        return [
            'days' => $days,
            'quality' => 'approx',
        ];
    }

    private function scheduleIsActiveOn(MedicationSchedule $schedule, Carbon $today): bool
    {
        if ($schedule->start_date instanceof Carbon && $today->lt($schedule->start_date->copy()->startOfDay())) {
            return false;
        }

        if ($schedule->end_date instanceof Carbon && $today->gt($schedule->end_date->copy()->startOfDay())) {
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

        if ($frequency === MedicationIntakeFrequency::DAILY) {
            return $unitsPerActiveDay;
        }

        if ($frequency === MedicationIntakeFrequency::WEEKDAYS) {
            $weekdays = $schedule->intake_weekdays;

            if (! is_array($weekdays) || $weekdays === []) {
                return null;
            }

            $count = count($weekdays);

            return ($unitsPerActiveDay * $count) / 7.0;
        }

        if (preg_match('/^every_(\d+)_days$/', $frequency, $matches) === 1) {
            $n = (int) $matches[1];

            if ($n < 2) {
                return null;
            }

            return $unitsPerActiveDay / (float) $n;
        }

        return null;
    }

    private function parsePositiveFloat(string $value): ?float
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $trimmed);

        if (! is_numeric($normalized)) {
            return null;
        }

        $float = (float) $normalized;

        if (! is_finite($float) || $float <= 0.0) {
            return null;
        }

        return $float;
    }
}
