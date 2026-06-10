<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum AppointmentStatus: string implements Castable
{
    use EncryptEnum;

    case SCHEDULED = 'scheduled';
    case DONE = 'done';
    case CANCELLED = 'cancelled';
}
