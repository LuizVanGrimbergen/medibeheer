<?php

namespace App\Enums;

enum MedicationPlanProposalStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case REVOKED = 'revoked';
}
