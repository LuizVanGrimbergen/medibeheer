<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\NewPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
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

                Log::notice('auth.password_reset.completed', [
                    'user_id' => $user->id,
                    'public_id' => $user->public_id,
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', trans($status));
        }

        Log::warning('auth.password_reset.failed', [
            'status' => $status,
            'email_hash' => $request->string('email')->toString(),
            'ip' => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
