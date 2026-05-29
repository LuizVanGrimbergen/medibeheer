<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum DailyMoodScore: string implements Castable
{
    use EncryptEnum;

    case BAD = 'bad';
    case OK = 'ok';
    case GOOD = 'good';
}
