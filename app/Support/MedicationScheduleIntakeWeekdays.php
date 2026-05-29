<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\MedicationIntakeFrequency;

final class MedicationScheduleIntakeWeekdays
{
    public static function normalizeNestedSchedule(array $schedule): array
    {
        $frequency = $schedule['intake_frequency'] ?? '';

        if ($frequency !== MedicationIntakeFrequency::WEEKDAYS) {
            $schedule['intake_weekdays'] = null;

            return $schedule;
        }

        $raw = $schedule['intake_weekdays'] ?? [];

        if (! is_array($raw)) {
            $schedule['intake_weekdays'] = [];

            return $schedule;
        }

        $ints = array_values(array_unique(array_filter(
            array_map(static fn (mixed $v): int => (int) $v, $raw),
            static fn (int $d): bool => $d >= 1 && $d <= 7,
        )));
        sort($ints);
        $schedule['intake_weekdays'] = $ints;

        return $schedule;
    }

    public static function normalizeFlatPayload(array $payload): array
    {
        $frequency = $payload['intake_frequency'] ?? '';

        if ($frequency !== MedicationIntakeFrequency::WEEKDAYS) {
            $payload['intake_weekdays'] = null;

            return $payload;
        }

        $raw = $payload['intake_weekdays'] ?? [];

        if (! is_array($raw)) {
            $payload['intake_weekdays'] = [];

            return $payload;
        }

        $ints = array_values(array_unique(array_filter(
            array_map(static fn (mixed $v): int => (int) $v, $raw),
            static fn (int $d): bool => $d >= 1 && $d <= 7,
        )));
        sort($ints);
        $payload['intake_weekdays'] = $ints;

        return $payload;
    }
}
