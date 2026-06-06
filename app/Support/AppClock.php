<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class AppClock
{
    public const string TIMEZONE = 'Europe/Brussels';

    public static function now(?CarbonInterface $now = null): CarbonImmutable
    {
        if ($now === null) {
            return CarbonImmutable::now(self::TIMEZONE);
        }

        return CarbonImmutable::parse($now)->timezone(self::TIMEZONE);
    }

    public static function today(?CarbonInterface $now = null): CarbonImmutable
    {
        return self::now($now)->startOfDay();
    }

    public static function startsAtLocal(Appointment $appointment): CarbonImmutable
    {
        $raw = $appointment->starts_at;

        return CarbonImmutable::create(
            (int) $raw->format('Y'),
            (int) $raw->format('m'),
            (int) $raw->format('d'),
            (int) $raw->format('H'),
            (int) $raw->format('i'),
            (int) $raw->format('s'),
            self::TIMEZONE,
        );
    }
}
