<?php

namespace App\Http\Controllers\Family\Invitations;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\RedirectsPatientCareTeamInvitationEntry;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FamilyInvitationEntryController extends Controller
{
    use RedirectsPatientCareTeamInvitationEntry;

    public function __invoke(Request $request): RedirectResponse
    {
        return $this->redirectForPatientCareTeamInvitationEntry($request);
    }

    protected function invitationEntryDestinationRoute(): string
    {
        return 'family.link';
    }

    protected function invitationEntryRegisterRole(): UserRole
    {
        return UserRole::FAMILY_MEMBER;
    }

    protected function invitationEntryWrongAccountTranslationKey(): string
    {
        return 'family_invitation.entry.wrong_account';
    }

    protected function userHasCorrectRoleForInvitationEntry(User $user): bool
    {
        return $user->isFamilyMember();
    }
}
