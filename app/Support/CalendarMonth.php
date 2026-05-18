<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

final class CalendarMonth
{
    public static function normalize(?string $raw): string
    {
        if ($raw === null || ! preg_match('/^\d{4}-(?<m>\d{2})$/', $raw, $matches)) {
            return CarbonImmutable::now()->format('Y-m');
        }

        $monthNum = (int) $matches['m'];

        if ($monthNum < 1 || $monthNum > 12) {
            return CarbonImmutable::now()->format('Y-m');
        }

        return $raw;
    }

    public static function fromRequest(Request $request, string $queryKey = 'calendar_month'): string
    {
        $raw = $request->query($queryKey);

        return self::normalize(is_string($raw) ? $raw : null);
    }
}
