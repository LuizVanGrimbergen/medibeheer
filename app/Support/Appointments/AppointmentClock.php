<?php

declare(strict_types=1);

namespace App\Support\Appointments;

use App\Models\Appointment;
use App\Support\AppClock;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class AppointmentClock
{
    public const string TIMEZONE = AppClock::TIMEZONE;

    public static function now(?CarbonInterface $now = null): CarbonImmutable
    {
        return AppClock::now($now);
    }

    public static function today(?CarbonInterface $now = null): CarbonImmutable
    {
        return AppClock::today($now);
    }

    public static function startsAtLocal(Appointment $appointment): CarbonImmutable
    {
        return AppClock::startsAtLocal($appointment);
    }
}
