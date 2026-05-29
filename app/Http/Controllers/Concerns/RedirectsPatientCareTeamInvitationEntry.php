<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsPatientCareTeamInvitationEntry
{
    abstract protected function invitationEntryDestinationRoute(): string;

    abstract protected function invitationEntryRegisterRole(): UserRole;

    abstract protected function invitationEntryWrongAccountTranslationKey(): string;

    abstract protected function userHasCorrectRoleForInvitationEntry(User $user): bool;

    protected function redirectForPatientCareTeamInvitationEntry(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            $request->session()->put('url.intended', route($this->invitationEntryDestinationRoute()));

            return redirect()->route('register', [
                'role' => $this->invitationEntryRegisterRole()->value,
            ]);
        }

        if (! $this->userHasCorrectRoleForInvitationEntry($user)) {
            return redirect()
                ->route('home')
                ->with('error', trans($this->invitationEntryWrongAccountTranslationKey()));
        }

        if (! $user->hasVerifiedEmail()) {
            $request->session()->put('url.intended', route($this->invitationEntryDestinationRoute()));

            return redirect()->route('verification.notice');
        }

        return redirect()->route($this->invitationEntryDestinationRoute());
    }
}
