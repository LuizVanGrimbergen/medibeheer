<?php

declare(strict_types=1);

namespace App\Support;

use App\Support\Medications\MedicationScheduleDoseTimes;

final class MedicationScheduleDoseTimeFields
{
    public static function normalizeNestedSchedule(array $schedule): array
    {
        if (array_key_exists('dose_time', $schedule) && is_string($schedule['dose_time'])) {
            $schedule['dose_time'] = trim($schedule['dose_time']);
        }

        if (array_key_exists('snooze_time', $schedule)) {
            $schedule['snooze_time'] = self::normalizeSnoozeTime(
                is_string($schedule['snooze_time']) ? $schedule['snooze_time'] : null,
                is_string($schedule['dose_time'] ?? null) ? $schedule['dose_time'] : '',
            );
        }

        return $schedule;
    }

    public static function normalizeFlatPayload(array $payload): array
    {
        if (array_key_exists('dose_time', $payload) && is_string($payload['dose_time'])) {
            $payload['dose_time'] = trim($payload['dose_time']);
        }

        if (array_key_exists('snooze_time', $payload)) {
            $payload['snooze_time'] = self::normalizeSnoozeTime(
                is_string($payload['snooze_time']) ? $payload['snooze_time'] : null,
                is_string($payload['dose_time'] ?? null) ? $payload['dose_time'] : '',
            );
        }

        return $payload;
    }

    private static function normalizeSnoozeTime(?string $snoozeTime, string $doseTime): string
    {
        $trimmed = trim($snoozeTime ?? '');

        if ($trimmed !== '') {
            return $trimmed;
        }

        $timeCount = count(MedicationScheduleDoseTimes::timeSegments($doseTime));

        if ($timeCount < 1) {
            return '';
        }

        return implode(', ', array_fill(0, $timeCount, (string) MedicationScheduleDoseTimes::DEFAULT_SNOOZE_MINUTES));
    }
}
