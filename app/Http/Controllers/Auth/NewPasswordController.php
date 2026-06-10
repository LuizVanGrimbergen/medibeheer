<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SecurityActivityDescription;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\NewPasswordRequest;
use App\Services\Audit\SecurityActivityLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
    ) {}

    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword/Index', [
            'email' => $request->string('email')->toString(),
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(NewPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            [
                'email_hash' => $request->string('email')->toString(),
                'password' => $request->string('password')->toString(),
                'password_confirmation' => $request->string('password_confirmation')->toString(),
                'token' => $request->string('token')->toString(),
            ],
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => $request->string('password')->toString(),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                $this->securityActivityLogger->record(
                    SecurityActivityDescription::AUTH_PASSWORD_RESET_COMPLETED,
                    causer: $user,
                    subject: $user,
                    properties: [
                        'public_id' => $user->public_id,
                    ],
                );
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', trans($status));
        }

        $this->securityActivityLogger->record(
            SecurityActivityDescription::AUTH_PASSWORD_RESET_FAILED,
            properties: [
                'status' => $status,
                'email_hash' => $request->string('email')->toString(),
            ],
        );

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
