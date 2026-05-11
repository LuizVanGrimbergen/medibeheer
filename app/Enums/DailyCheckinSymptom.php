<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum DailyCheckinSymptom: string implements Castable
{
    use EncryptEnum;

    case PAIN = 'pain';
    case FATIGUE = 'fatigue';
    case DIZZINESS = 'dizziness';
    case SHORTNESS_OF_BREATH = 'shortness_of_breath';
    case NAUSEA = 'nausea';
    case POOR_SLEEP = 'poor_sleep';
    case LONELINESS = 'loneliness';
    case ANXIETY_OR_WORRY = 'anxiety_or_worry';
    case POOR_APPETITE = 'poor_appetite';
    case STIFF_OR_JOINT_PAIN = 'stiff_or_joint_pain';
}
