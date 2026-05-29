<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SecurityActivityDescription;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetLinkRequest;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
    ) {}

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
        $email = $request->validated('email');
        $user = User::findByEmail($email);
        $emailHash = $user?->email_hash ?? User::hashEmail($email);

        $status = Password::sendResetLink(
            ['email_hash' => $emailHash]
        );

        if ($status === Password::RESET_LINK_SENT) {
            $this->securityActivityLogger->record(
                SecurityActivityDescription::AUTH_PASSWORD_RESET_LINK_SENT,
                causer: $user,
                properties: [
                    'email_hash' => $emailHash,
                ],
            );
        } else {
            $this->securityActivityLogger->record(
                SecurityActivityDescription::AUTH_PASSWORD_RESET_LINK_FAILED,
                properties: [
                    'status' => $status,
                    'email_hash' => $emailHash,
                ],
            );
        }

        return back()->with('status', trans('passwords.sent'));
    }
}
