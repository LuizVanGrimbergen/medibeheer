<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationNoticeController extends Controller
{
    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Display the email verification notice.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser === null) {
            return redirect()->route('login');
        }

        return $authenticatedUser->hasVerifiedEmail()
            ? redirect()->intended($authenticatedUser->defaultAuthenticatedHomeUrl())
            : Inertia::render('Auth/VerifyEmail', ['status' => session('status')]);
    }
}
