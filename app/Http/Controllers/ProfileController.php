<?php

namespace App\Http\Controllers;

use App\Enums\SecurityActivityDescription;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;
use App\Services\Audit\UserSecurityActivityScreenService;
use App\Services\Privacy\UserDataErasureService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
        private readonly UserSecurityActivityScreenService $userSecurityActivityScreenService,
        private readonly UserDataErasureService $userDataErasureService,
    ) {}

    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $this->authorize('view', $user);

        $payload = [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'securityActivities' => null,
        ];

        if ($request->string('section')->toString() === 'security-activity') {
            $payload['securityActivities'] = $this->userSecurityActivityScreenService->paginatedForUser($user);
        }

        return Inertia::render('Settings/Edit/Index', $payload);
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

        $this->securityActivityLogger->record(
            SecurityActivityDescription::USER_PROFILE_UPDATED,
            causer: $request->user(),
            subject: $request->user(),
            properties: [
                'public_id' => $request->user()->public_id,
                'name_changed' => $previousName !== $request->user()->name,
                'email_changed' => $emailChanged,
            ],
        );

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

        if (! $user instanceof User) {
            abort(403);
        }

        Auth::logout();

        $this->securityActivityLogger->record(
            SecurityActivityDescription::USER_ACCOUNT_DELETED,
            causer: $user,
            subject: $user,
            properties: [
                'public_id' => $user->public_id,
            ],
        );

        $this->userDataErasureService->eraseUserRelatedRecords($user);

        User::destroy($user->getKey());

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
