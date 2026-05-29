<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser === null) {
            return redirect()->route('login');
        }

        if ($authenticatedUser->hasVerifiedEmail()) {
            return redirect()->intended($authenticatedUser->defaultAuthenticatedHomeUrl().'?verified=1');
        }

        if ($authenticatedUser->markEmailAsVerified()) {
            event(new Verified($authenticatedUser));
        }

        return redirect()->intended($authenticatedUser->defaultAuthenticatedHomeUrl().'?verified=1');
    }
}
