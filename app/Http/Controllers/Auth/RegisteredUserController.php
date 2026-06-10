<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SecurityActivityDescription;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;
use App\Services\Privacy\UserConsentRecorder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
        private readonly UserConsentRecorder $userConsentRecorder,
    ) {}

    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Display the registration view.
     */
    public function create(Request $request, ResolveSelectedRole $resolveSelectedRole): Response
    {
        return Inertia::render('Auth/Register/Index', [
            'selectedRole' => $resolveSelectedRole($request),
            'roleTokens' => UserRole::encryptedTransportTokens(),
            'privacyPolicyVersion' => config('privacy.policy_version'),
            'termsVersion' => config('legal.terms_version'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        event(new Registered($user));

        $this->userConsentRecorder->recordRegistrationConsents($user, $request);

        Auth::login($user, true);
        $request->session()->regenerate();

        $this->securityActivityLogger->record(
            SecurityActivityDescription::AUTH_REGISTRATION_SUCCEEDED,
            causer: $user,
            subject: $user,
            properties: [
                'public_id' => $user->public_id,
                'role' => $user->role->value,
            ],
        );

        return redirect()->route('verification.notice');
    }
}
