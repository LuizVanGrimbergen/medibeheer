<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case DONE = 'done';
    case CANCELLED = 'cancelled';
}
