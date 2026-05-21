<?php

declare(strict_types=1);

namespace App\Support\Medications;

final class PatientMedicationReminderTranslations
{
    public const string LOCALE = 'nl';

    public static function trans(string $key, array $replace = []): string
    {
        return trans($key, $replace, self::LOCALE);
    }
}
