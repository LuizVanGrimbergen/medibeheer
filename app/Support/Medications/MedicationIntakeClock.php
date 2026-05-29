<?php

declare(strict_types=1);

namespace App\Support\Medications;

use Carbon\CarbonImmutable;

final class MedicationIntakeClock
{
    public const string TIMEZONE = 'Europe/Brussels';

    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::TIMEZONE);
    }

    public static function today(): CarbonImmutable
    {
        return self::now()->startOfDay();
    }
}
