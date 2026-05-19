<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

final class FamilyUpdatesPeriodDays
{
    public const DEFAULT = 3;

    public const ALLOWED = [1, 3, 5];

    public static function normalize(int|string|null $raw): int
    {
        $parsed = is_numeric($raw) ? (int) $raw : self::DEFAULT;

        if (! in_array($parsed, self::ALLOWED, true)) {
            return self::DEFAULT;
        }

        return $parsed;
    }

    public static function fromRequest(Request $request, string $queryKey = 'period_days'): int
    {
        $raw = $request->query($queryKey);

        if (is_int($raw) || is_string($raw)) {
            return self::normalize($raw);
        }

        return self::DEFAULT;
    }
}
