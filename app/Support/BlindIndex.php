<?php

declare(strict_types=1);

namespace App\Support;

use BackedEnum;

final class BlindIndex
{
    public static function hash(string $value): string
    {
        return hash_hmac('sha256', $value, self::key());
    }

    public static function forEnum(BackedEnum $enum): string
    {
        return self::hash($enum->value);
    }

    private static function key(): string
    {
        $emailHashKey = config('app.email_hash_key');

        if (is_string($emailHashKey) && $emailHashKey !== '') {
            return $emailHashKey;
        }

        return (string) config('app.key');
    }
}
