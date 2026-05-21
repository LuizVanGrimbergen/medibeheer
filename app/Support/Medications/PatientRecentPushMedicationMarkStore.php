<?php

declare(strict_types=1);

namespace App\Support\Medications;

use Illuminate\Support\Facades\Cache;

final class PatientRecentPushMedicationMarkStore
{
    private const int TTL_MINUTES = 120;

    private static function cacheKey(int $patientId): string
    {
        return "patient:{$patientId}:recent-push-medication-mark";
    }

    public function remember(int $patientId, string $medicationName): void
    {
        Cache::put(
            self::cacheKey($patientId),
            [
                'medication_name' => $medicationName,
            ],
            now()->addMinutes(self::TTL_MINUTES),
        );
    }

    public function peek(int $patientId): ?string
    {
        $payload = Cache::get(self::cacheKey($patientId));

        if (! is_array($payload)) {
            return null;
        }

        $name = (string) ($payload['medication_name'] ?? '');

        if ($name === '') {
            return null;
        }

        return $name;
    }

    public function forget(int $patientId): void
    {
        Cache::forget(self::cacheKey($patientId));
    }
}
