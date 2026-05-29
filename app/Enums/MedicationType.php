<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum MedicationType: string implements Castable
{
    use EncryptEnum;

    case PILL = 'pill';
    case LIQUID = 'liquid';
    case INJECTION = 'injection';
    case CREAM = 'cream';
    case SACHETS = 'sachets';
    case OTHER = 'other';
}
