<?php

namespace App\Enums;

enum DoctorType: string
{
    case DENTIST = 'dentist';
    case HOSPITAL = 'hospital';
    case GENERAL_PRACTITIONER = 'general_practitioner';
    case SPECIALIST = 'specialist';
}
