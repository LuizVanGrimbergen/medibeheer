<?php

namespace App\Http\Controllers\Doctor\Invitations;

use App\Http\Controllers\Concerns\AcceptsIncomingPatientCareTeamInvitation;
use App\Http\Controllers\Controller;
use App\Models\DoctorInvitation;
use App\Services\Doctor\DoctorInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcceptIncomingDoctorInvitationController extends Controller
{
    use AcceptsIncomingPatientCareTeamInvitation;

    public function __invoke(
        Request $request,
        DoctorInvitation $incomingDoctorInvitation,
        DoctorInvitationService $service,
    ): RedirectResponse {
        $service->acceptInvitation($request->user(), $incomingDoctorInvitation);

        return $this->redirectAfterIncomingInvitationAccepted();
    }

    protected function incomingInvitationAcceptRedirectRoute(): string
    {
        return 'doctor.patients';
    }

    protected function incomingInvitationLinkedFlashTranslationKey(): string
    {
        return 'doctor_invitation.flash.linked';
    }
}
