<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum DoctorType: string implements Castable
{
    use EncryptEnum;

    case DENTIST = 'dentist';
    case HOSPITAL = 'hospital';
    case GENERAL_PRACTITIONER = 'general_practitioner';
    case SPECIALIST = 'specialist';
}
