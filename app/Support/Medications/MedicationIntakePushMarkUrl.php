<?php

declare(strict_types=1);

namespace App\Support\Medications;

use Illuminate\Support\Facades\URL;

final class MedicationIntakePushMarkUrl
{
    public static function forSlot(array $slot): string
    {
        return URL::temporarySignedRoute(
            'patient.medication-intakes.mark-from-push',
            MedicationIntakeClock::today()->endOfDay(),
            [
                'medicationSchedule' => (int) $slot['medication_schedule_id'],
                'doseTime' => (string) $slot['dose_time'],
            ],
        );
    }
}
