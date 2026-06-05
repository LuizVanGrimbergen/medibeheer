<?php

declare(strict_types=1);

namespace App\Support\Medications\PushReminders;

use App\Support\Medications\MedicationUrgencyToneResolver;

enum PushReminderTier: string
{
    case Critical = 'critical';
    case Urgent = 'urgent';

    public function maxDays(): int
    {
        return match ($this) {
            self::Critical => MedicationUrgencyToneResolver::CRITICAL_MAX_DAYS,
            self::Urgent => MedicationUrgencyToneResolver::REMINDER_URGENT_MAX_DAYS,
        };
    }

    /**
     * @return list<self>
     */
    public static function forDaysRemaining(int $daysRemaining): array
    {
        $tiers = [];

        if ($daysRemaining <= self::Critical->maxDays()) {
            $tiers[] = self::Critical;
        }

        if ($daysRemaining <= self::Urgent->maxDays()) {
            $tiers[] = self::Urgent;
        }

        return $tiers;
    }
}
