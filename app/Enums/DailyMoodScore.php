<?php

namespace App\Enums;

use App\Enums\Concerns\CastsEncryptedInDatabase;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum DailyMoodScore: string implements Castable
{
    use CastsEncryptedInDatabase;

    case BAD = 'bad';
    case OK = 'ok';
    case GOOD = 'good';
}
