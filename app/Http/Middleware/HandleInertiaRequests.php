<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\FamilyDashboardState;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        $shared = [
            ...parent::share($request),
            'auth' => [
                'user' => function () use ($request, $user): ?array {
                    if ($user === null) {
                        return null;
                    }

                    $email = null;

                    if ($request->routeIs('settings.edit')) {
                        $email = $user->email;
                    }

                    return [
                        'public_id' => $user->public_id,
                        'name' => $user->name,
                        'email' => $email,
                        'role' => $user->role?->value,
                        'email_verified_at' => $user->email_verified_at?->toISOString(),
                    ];
                },
            ],
            'flash' => [
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
                'rateLimitSeconds' => fn () => $request->session()->get('rate_limit_seconds'),
                'daily_checkin_mood' => fn () => $request->session()->get('daily_checkin_mood'),
            ],
            'legal' => [
                'privacyUrl' => route('legal.privacy', absolute: false),
                'cookiesUrl' => route('legal.cookies', absolute: false),
                'policyVersion' => config('privacy.policy_version'),
            ],
        ];

        if ($user instanceof User && $user->isFamilyMember() && $request->routeIs('family.*')) {
            $shared['family'] = fn (): array => FamilyDashboardState::inertiaPayload($request);
        }

        return $shared;
    }
}
