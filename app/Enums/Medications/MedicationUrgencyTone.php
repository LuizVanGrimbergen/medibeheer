<?php

namespace App\Enums\Medications;

enum MedicationUrgencyTone: string
{
    case CRITICAL = 'critical';
    case WARNING = 'warning';
    case SAFE = 'safe';
}
