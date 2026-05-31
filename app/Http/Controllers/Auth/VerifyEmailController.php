<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Mark the user's email address as verified and start their session.
     */
    public function __invoke(VerifyEmailRequest $request): RedirectResponse
    {
        $user = $request->verifiedUser();

        if ($user === null) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            $this->authenticateVerifiedUser($request, $user);

            return redirect()->intended($user->defaultAuthenticatedHomeUrl().'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $this->authenticateVerifiedUser($request, $user);

        return redirect()->intended($user->defaultAuthenticatedHomeUrl().'?verified=1');
    }

    private function authenticateVerifiedUser(Request $request, User $user): void
    {
        Auth::login($user, remember: true);
        $request->session()->regenerate();
    }
}
