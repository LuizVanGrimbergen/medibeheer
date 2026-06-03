<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;

enum UserRole: string
{
    use EncryptEnum;

    case PATIENT = 'patient';
    case DOCTOR = 'doctor';
    case FAMILY_MEMBER = 'family_member';
}
