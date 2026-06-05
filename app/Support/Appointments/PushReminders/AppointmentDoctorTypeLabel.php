<?php

declare(strict_types=1);

namespace App\Support\Appointments\PushReminders;

use App\Enums\DoctorType;
use App\Support\PushReminders\ReminderTranslations;

final class AppointmentDoctorTypeLabel
{
    public static function forType(DoctorType|string $type): string
    {
        $value = $type instanceof DoctorType ? $type->value : $type;
        $key = "appointment_reminders.doctor_types.{$value}";
        $translated = ReminderTranslations::trans($key);

        if ($translated === $key) {
            return ReminderTranslations::trans('appointment_reminders.doctor_types.fallback');
        }

        return $translated;
    }
}
