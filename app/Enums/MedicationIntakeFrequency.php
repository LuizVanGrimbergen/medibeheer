<?php

declare(strict_types=1);

namespace App\Enums;

abstract class MedicationIntakeFrequency
{
    public const string DAILY = 'daily';

    public const string WEEKDAYS = 'weekdays';

    public static function everyNDaysValue(int $n): string
    {
        return "every_{$n}_days";
    }

    public static function parseEveryNDays(string $value): ?int
    {
        if ($value === self::DAILY || $value === self::WEEKDAYS) {
            return null;
        }

        if (preg_match('/^every_(\d+)_days$/', $value, $matches) !== 1) {
            return null;
        }

        $n = (int) $matches[1];

        if ($n < 2) {
            return null;
        }

        return $n;
    }

    public static function allowedValues(): array
    {
        static $values = null;

        if ($values !== null) {
            return $values;
        }

        $built = [self::DAILY];

        for ($n = 2; $n <= 60; $n++) {
            $built[] = self::everyNDaysValue($n);
        }

        $built[] = self::WEEKDAYS;

        return $values = $built;
    }
}
