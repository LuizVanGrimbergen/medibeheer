<?php

declare(strict_types=1);

namespace App\Support\Medications;

use App\Support\AppClock;
use Carbon\CarbonImmutable;

final class MedicationIntakeClock
{
    public const string TIMEZONE = AppClock::TIMEZONE;

    public static function now(): CarbonImmutable
    {
        return AppClock::now();
    }

    public static function today(): CarbonImmutable
    {
        return AppClock::today();
    }
}
