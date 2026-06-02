<?php

namespace App\Enums;

enum MedicationPrescriptionPickupStatus: string
{
    case PENDING = 'pending';
    case PICKED_UP = 'picked_up';
}
