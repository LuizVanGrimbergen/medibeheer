<?php

declare(strict_types=1);

namespace App\Support\Medications\PushReminders\Intake;

use App\Support\Medications\MedicationIntakeClock;

final class ReminderCache
{
    public const string DUE_PREFIX = 'patient-medication-due-reminder';

    public const string MISSED_PREFIX = 'patient-medication-missed-reminder';

    public function dueCacheKey(
        int $patientId,
        int $scheduleId,
        string $doseTime,
        string $dateKey,
    ): string {
        return $this->cacheKey(self::DUE_PREFIX, $patientId, $scheduleId, $doseTime, $dateKey);
    }

    public function missedCacheKey(
        int $patientId,
        int $scheduleId,
        string $doseTime,
        string $dateKey,
    ): string {
        return $this->cacheKey(self::MISSED_PREFIX, $patientId, $scheduleId, $doseTime, $dateKey);
    }

    public function ttlUntilEndOfDay(): \DateTimeInterface
    {
        return MedicationIntakeClock::today()->endOfDay();
    }

    private function cacheKey(
        string $prefix,
        int $patientId,
        int $scheduleId,
        string $doseTime,
        string $dateKey,
    ): string {
        return "{$prefix}:{$patientId}:{$scheduleId}:{$doseTime}:{$dateKey}";
    }
}
