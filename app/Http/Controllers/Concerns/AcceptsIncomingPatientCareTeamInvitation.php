<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;

trait AcceptsIncomingPatientCareTeamInvitation
{
    abstract protected function incomingInvitationAcceptRedirectRoute(): string;

    abstract protected function incomingInvitationLinkedFlashTranslationKey(): string;

    protected function redirectAfterIncomingInvitationAccepted(): RedirectResponse
    {
        return redirect()
            ->route($this->incomingInvitationAcceptRedirectRoute())
            ->with('success', trans($this->incomingInvitationLinkedFlashTranslationKey()));
    }
}
