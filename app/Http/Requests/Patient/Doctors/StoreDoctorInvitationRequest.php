<?php

namespace App\Http\Requests\Patient\Doctors;

use App\Enums\UserRole;
use App\Http\Requests\Patient\StorePatientCareTeamInvitationRequest;

class StoreDoctorInvitationRequest extends StorePatientCareTeamInvitationRequest
{
    protected function inviteeRole(): UserRole
    {
        return UserRole::DOCTOR;
    }

    protected function translationKey(): string
    {
        return 'doctor_invitation';
    }
}
