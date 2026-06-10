<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum MedicationPlanProposalStatus: string implements Castable
{
    use EncryptEnum;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case REVOKED = 'revoked';
}
