<?php

declare(strict_types=1);

namespace App\Support\Medications\PushReminders;

enum PushReminderAudience: string
{
    case Patient = 'patient';
    case Family = 'family';
}
