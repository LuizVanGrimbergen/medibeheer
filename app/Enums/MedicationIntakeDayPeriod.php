<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\Medications\DoseTime;
use InvalidArgumentException;

enum MedicationIntakeDayPeriod: string
{
    case MORNING = 'morning';
    case AFTERNOON = 'afternoon';
    case EVENING = 'evening';
    case NIGHT = 'night';

    public function sortRank(): int
    {
        return match ($this) {
            self::MORNING => 0,
            self::AFTERNOON => 1,
            self::EVENING => 2,
            self::NIGHT => 3,
        };
    }

    public static function forDoseTime(DoseTime $time): self
    {
        $minutes = $time->minutesSinceMidnight();

        return match (true) {
            $minutes >= 22 * 60 || $minutes < 5 * 60 => self::NIGHT,
            $minutes < 12 * 60 => self::MORNING,
            $minutes < 17 * 60 => self::AFTERNOON,
            default => self::EVENING,
        };
    }

    public static function fromDoseTime(string $doseTime): self
    {
        $time = DoseTime::tryFrom($doseTime);

        if ($time === null) {
            throw new InvalidArgumentException("Invalid dose time [{$doseTime}].");
        }

        return self::forDoseTime($time);
    }
}
