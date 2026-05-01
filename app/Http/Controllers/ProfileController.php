<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $this->authorize('view', $request->user());

        return Inertia::render('Settings/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->authorize('update', $request->user());

        $previousEmailHash = $request->user()->email_hash;
        $previousName = $request->user()->name;

        $emailChanged = false;

        $request->user()->fill($request->safe()->except('current_password'));

        if ($previousEmailHash !== $request->user()->email_hash) {
            $request->user()->email_verified_at = null;
            $emailChanged = true;
        }

        $request->user()->save();

        Log::notice('user.profile.updated', [
            'user_id' => $request->user()->id,
            'public_id' => $request->user()->public_id,
            'name_changed' => $previousName !== $request->user()->name,
            'email_changed' => $previousEmailHash !== $request->user()->email_hash,
        ]);

        if ($emailChanged) {
            $request->user()->sendEmailVerificationNotification();
            $request->session()->flash('status', 'verification-link-sent');
        }

        return Redirect::route('settings.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', $request->user());
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        Log::alert('user.account.deleted', [
            'user_id' => $user->id,
            'public_id' => $user->public_id,
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
