<?php

namespace App\Enums;

enum MedicationListStatus: string
{
    case ACTIVE = 'active';
    case ENDED = 'ended';
    case REMOVED = 'removed';
}
