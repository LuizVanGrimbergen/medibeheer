<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetLinkRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    private const int THROTTLE_MAX_ATTEMPTS = 3;

    private const int THROTTLE_DECAY_SECONDS = 60;

    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(PasswordResetLinkRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::THROTTLE_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($validated)
                ->with('error', trans('auth.throttle', ['seconds' => $seconds]))
                ->with('rate_limit_seconds', $seconds);
        }

        $email = $validated['email'];
        $user = User::findByEmail($email);
        $emailHash = $user?->email_hash ?? User::hashEmail($email);

        RateLimiter::hit($throttleKey, self::THROTTLE_DECAY_SECONDS);

        $status = Password::sendResetLink(
            ['email_hash' => $emailHash]
        );

        if ($status === Password::RESET_LINK_SENT) {
            Log::notice('auth.password_reset_link.sent', [
                'email_hash' => $emailHash,
                'ip' => $request->ip(),
            ]);
        } else {
            Log::warning('auth.password_reset_link.failed', [
                'status' => $status,
                'email_hash' => $emailHash,
                'ip' => $request->ip(),
            ]);
        }

        return back()->with('status', trans('passwords.sent'));
    }

    private function throttleKey(PasswordResetLinkRequest $request): string
    {
        return User::hashEmail($request->string('email')->toString()).'|'.$request->ip();
    }
}
