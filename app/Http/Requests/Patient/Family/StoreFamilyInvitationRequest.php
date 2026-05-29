<?php

namespace App\Http\Requests\Patient\Family;

use App\Enums\UserRole;
use App\Http\Requests\Patient\StorePatientCareTeamInvitationRequest;

class StoreFamilyInvitationRequest extends StorePatientCareTeamInvitationRequest
{
    protected function inviteeRole(): UserRole
    {
        return UserRole::FAMILY_MEMBER;
    }

    protected function translationKey(): string
    {
        return 'family_invitation';
    }
}
