<?php

declare(strict_types=1);

namespace App\Enums;

enum MedicationIntakeDayStatus: string
{
    case NO_SCHEDULE = 'no_schedule';
    case NONE_TAKEN = 'none_taken';
    case PARTIAL = 'partial';
    case COMPLETE = 'complete';

    public static function fromCounts(int $scheduledCount, int $takenCount): self
    {
        if ($scheduledCount === 0) {
            return self::NO_SCHEDULE;
        }

        if ($takenCount === 0) {
            return self::NONE_TAKEN;
        }

        if ($takenCount < $scheduledCount) {
            return self::PARTIAL;
        }

        return self::COMPLETE;
    }
}
