<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Display the login view.
     */
    public function create(Request $request, ResolveSelectedRole $resolveSelectedRole): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'selectedRole' => $resolveSelectedRole($request),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->authenticate();

        $request->session()->regenerate();

        $authenticatedUser = $request->user();

        if ($authenticatedUser === null) {
            return redirect()->route('login');
        }

        if (! $authenticatedUser->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended($authenticatedUser->defaultAuthenticatedHomeUrl());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser !== null) {
            Log::info('auth.logout', [
                'public_id' => $authenticatedUser->public_id ?? null,
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
