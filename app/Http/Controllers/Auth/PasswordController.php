<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SecurityActivityDescription;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\Audit\SecurityActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller
{
    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
    ) {}

    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Update the user's password.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        $validated = $request->validated();

        Auth::logoutOtherDevices($validated['current_password']);

        $authenticatedUser->update([
            'password' => $validated['password'],
        ]);

        $this->securityActivityLogger->record(
            SecurityActivityDescription::AUTH_PASSWORD_UPDATED,
            causer: $authenticatedUser,
            subject: $authenticatedUser,
            properties: [
                'public_id' => $authenticatedUser->public_id,
            ],
        );

        return back();
    }
}
