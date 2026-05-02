<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmablePasswordController extends Controller
{
    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Show the confirm password view.
     */
    public function show(Request $request): Response
    {
        $authenticatedUser = $request->user();
        $fallbackHome = $authenticatedUser !== null
            ? route($authenticatedUser->defaultAuthenticatedHomeRoute())
            : route('dashboard');

        $backUrl = url()->previous();
        $applicationHost = parse_url(config('app.url'), PHP_URL_HOST);
        $backUrlHost = parse_url($backUrl, PHP_URL_HOST);

        if ($backUrl === $request->url()) {
            $backUrl = $fallbackHome;
        }

        if ($applicationHost !== null && $backUrlHost !== null && $applicationHost !== $backUrlHost) {
            $backUrl = $fallbackHome;
        }

        return Inertia::render('Auth/ConfirmPassword', [
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        if (! Hash::check($request->string('password')->toString(), $authenticatedUser->password)) {
            throw ValidationException::withMessages([
                'password' => trans('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended($authenticatedUser->defaultAuthenticatedHomeUrl());
    }
}
