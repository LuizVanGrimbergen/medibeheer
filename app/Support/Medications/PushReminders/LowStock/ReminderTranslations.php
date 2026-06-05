<?php

declare(strict_types=1);

namespace App\Support\Medications\PushReminders\LowStock;

final class ReminderTranslations
{
    public const string LOCALE = 'nl';

    public static function trans(string $key, array $replace = []): string
    {
        return trans($key, $replace, self::LOCALE);
    }
}
