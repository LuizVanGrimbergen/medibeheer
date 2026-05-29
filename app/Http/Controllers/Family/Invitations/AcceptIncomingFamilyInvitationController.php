<?php

namespace App\Http\Controllers\Family\Invitations;

use App\Http\Controllers\Concerns\AcceptsIncomingPatientCareTeamInvitation;
use App\Http\Controllers\Controller;
use App\Models\FamilyInvitation;
use App\Services\Family\FamilyInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcceptIncomingFamilyInvitationController extends Controller
{
    use AcceptsIncomingPatientCareTeamInvitation;

    public function __invoke(
        Request $request,
        FamilyInvitation $incomingFamilyInvitation,
        FamilyInvitationService $service,
    ): RedirectResponse {
        $service->acceptInvitation($request->user(), $incomingFamilyInvitation);

        return $this->redirectAfterIncomingInvitationAccepted();
    }

    protected function incomingInvitationAcceptRedirectRoute(): string
    {
        return 'family.link';
    }

    protected function incomingInvitationLinkedFlashTranslationKey(): string
    {
        return 'family_invitation.flash.linked';
    }
}
