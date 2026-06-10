<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum MedicationPrescriptionPickupStatus: string implements Castable
{
    use EncryptEnum;

    case PENDING = 'pending';
    case PICKED_UP = 'picked_up';
}
