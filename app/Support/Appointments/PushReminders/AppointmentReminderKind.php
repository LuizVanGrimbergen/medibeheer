<?php

declare(strict_types=1);

namespace App\Support\Appointments\PushReminders;

enum AppointmentReminderKind: string
{
    case TwoDaysBefore = 'two_days_before';
    case TwoHoursBefore = 'two_hours_before';
}
