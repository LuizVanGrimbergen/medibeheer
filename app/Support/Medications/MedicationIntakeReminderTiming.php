<?php

declare(strict_types=1);

namespace App\Support\Medications;

use Carbon\CarbonInterface;

final class MedicationIntakeReminderTiming
{
    public static function isExactDoseTimeMinute(string $doseTime, ?CarbonInterface $now = null): bool
    {
        $now = $now ?? MedicationIntakeClock::now();
        $parsed = DoseTime::tryFrom($doseTime);

        if ($parsed === null) {
            return false;
        }

        $doseMinute = $now->copy()->startOfDay()->addMinutes($parsed->minutesSinceMidnight());

        return $now->format('Y-m-d H:i') === $doseMinute->format('Y-m-d H:i');
    }

    public static function isExactSnoozeEndMinute(
        string $doseTime,
        int $snoozeMinutes,
        ?CarbonInterface $now = null,
    ): bool {
        if ($snoozeMinutes < 1) {
            return false;
        }

        $now = $now ?? MedicationIntakeClock::now();
        $parsed = DoseTime::tryFrom($doseTime);

        if ($parsed === null) {
            return false;
        }

        $windowEnd = $now->copy()->startOfDay()->addMinutes(
            $parsed->minutesSinceMidnight() + $snoozeMinutes,
        );

        return $now->format('Y-m-d H:i') === $windowEnd->format('Y-m-d H:i');
    }
}
