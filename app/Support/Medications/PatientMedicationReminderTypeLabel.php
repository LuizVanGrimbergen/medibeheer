<?php

declare(strict_types=1);

namespace App\Support\Medications;

use App\Enums\MedicationType;

final class PatientMedicationReminderTypeLabel
{
    public static function forType(MedicationType|string $type): string
    {
        $value = $type instanceof MedicationType ? $type->value : $type;
        $key = "patient_medication_reminders.types.{$value}";
        $translated = PatientMedicationReminderTranslations::trans($key);

        if ($translated === $key) {
            return $value;
        }

        return $translated;
    }
}
