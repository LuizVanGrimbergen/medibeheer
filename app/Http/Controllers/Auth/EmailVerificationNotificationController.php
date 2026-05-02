<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser === null) {
            return redirect()->route('login');
        }

        if ($authenticatedUser->hasVerifiedEmail()) {
            return redirect()->intended($authenticatedUser->defaultAuthenticatedHomeUrl());
        }

        $authenticatedUser->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
