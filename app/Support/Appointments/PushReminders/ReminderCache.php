<?php

declare(strict_types=1);

namespace App\Support\Appointments\PushReminders;

final class ReminderCache
{
    private const string KEY_PREFIX = 'appointment-reminder';

    public function cacheKey(
        AppointmentReminderKind $kind,
        int $recipientUserId,
        int $appointmentId,
    ): string {
        return self::KEY_PREFIX.":{$kind->value}:{$recipientUserId}:{$appointmentId}";
    }
}
