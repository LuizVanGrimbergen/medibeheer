<?php

namespace App\Enums;

enum UserRole: string
{
    case PATIENT = 'patient';
    case DOCTOR = 'doctor';
    case FAMILY_MEMBER = 'family_member';
}
