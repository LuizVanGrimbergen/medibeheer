<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller
{
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

        return back();
    }
}
