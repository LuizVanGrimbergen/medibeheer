<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum UserRole: string implements Castable
{
    use EncryptEnum;

    case PATIENT = 'patient';
    case DOCTOR = 'doctor';
    case FAMILY_MEMBER = 'family_member';
}
