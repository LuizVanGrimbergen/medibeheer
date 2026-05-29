<?php

namespace App\Http\Controllers\Doctor\Invitations;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\RedirectsPatientCareTeamInvitationEntry;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DoctorInvitationEntryController extends Controller
{
    use RedirectsPatientCareTeamInvitationEntry;

    public function __invoke(Request $request): RedirectResponse
    {
        return $this->redirectForPatientCareTeamInvitationEntry($request);
    }

    protected function invitationEntryDestinationRoute(): string
    {
        return 'doctor.patients';
    }

    protected function invitationEntryRegisterRole(): UserRole
    {
        return UserRole::DOCTOR;
    }

    protected function invitationEntryWrongAccountTranslationKey(): string
    {
        return 'doctor_invitation.entry.wrong_account';
    }

    protected function userHasCorrectRoleForInvitationEntry(User $user): bool
    {
        return $user->isDoctor();
    }
}
