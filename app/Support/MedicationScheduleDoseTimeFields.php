<?php

declare(strict_types=1);

namespace App\Support;

use App\Support\Medications\MedicationScheduleDoseTimes;

final class MedicationScheduleDoseTimeFields
{
    public static function normalizeNestedSchedule(array $schedule): array
    {
        if (! array_key_exists('dose_time', $schedule)) {
            unset($schedule['snooze_time']);

            return $schedule;
        }

        $doseTime = is_string($schedule['dose_time']) ? trim($schedule['dose_time']) : '';
        $snoozeTime = is_string($schedule['snooze_time'] ?? null)
            ? trim((string) $schedule['snooze_time'])
            : '';

        if ($snoozeTime !== '') {
            $schedule['dose_time'] = MedicationScheduleDoseTimes::mergeDisplayTimesAndSnoozes(
                $doseTime,
                $snoozeTime,
            );
        }

        unset($schedule['snooze_time']);

        return $schedule;
    }

    public static function normalizeFlatPayload(array $payload): array
    {
        if (! array_key_exists('dose_time', $payload)) {
            unset($payload['snooze_time']);

            return $payload;
        }

        $doseTime = is_string($payload['dose_time']) ? trim($payload['dose_time']) : '';
        $snoozeTime = is_string($payload['snooze_time'] ?? null)
            ? trim((string) $payload['snooze_time'])
            : '';

        if ($snoozeTime !== '') {
            $payload['dose_time'] = MedicationScheduleDoseTimes::mergeDisplayTimesAndSnoozes(
                $doseTime,
                $snoozeTime,
            );
        }

        unset($payload['snooze_time']);

        return $payload;
    }
}
