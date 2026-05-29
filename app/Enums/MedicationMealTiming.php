<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum MedicationMealTiming: string implements Castable
{
    use EncryptEnum;

    case BEFORE_FOOD = 'before_food';
    case AFTER_FOOD = 'after_food';
    case WITH_FOOD = 'with_food';
    case UNRELATED = 'unrelated';
}
