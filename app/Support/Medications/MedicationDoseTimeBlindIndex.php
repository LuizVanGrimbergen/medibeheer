<?php

declare(strict_types=1);

namespace App\Support\Medications;

final class MedicationDoseTimeBlindIndex
{
    public static function hash(string $doseTime): string
    {
        return hash_hmac('sha256', self::normalize($doseTime), (string) config('app.key'));
    }

    public static function normalize(string $doseTime): string
    {
        return trim($doseTime);
    }
}
