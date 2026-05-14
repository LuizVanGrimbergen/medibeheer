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
