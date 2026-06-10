<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\FamilyDashboardState;
use App\Support\PatientNavigationState;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
                'daily_checkin_encouragement' => fn () => $request->session()->get('daily_checkin_encouragement'),
            ],
            'legal' => [
                'privacyUrl' => route('legal.privacy', absolute: false),
                'cookiesUrl' => route('legal.cookies', absolute: false),
                'termsUrl' => route('legal.terms', absolute: false),
                'policyVersion' => config('privacy.policy_version'),
                'termsVersion' => config('legal.terms_version'),
            ],
            'seo' => fn (): array => [
                'indexable' => Seo::shouldIndex($request),
                'siteName' => config('app.name'),
                'description' => config('seo.description'),
                'canonicalUrl' => Seo::canonicalUrl($request),
                'ogImageUrl' => Seo::ogImageUrl(),
                'locale' => Seo::openGraphLocale(),
            ],
        ];

        if ($user instanceof User && $user->isFamilyMember() && $request->routeIs('family.*')) {
            $shared['family'] = Inertia::defer(
                fn (): array => FamilyDashboardState::inertiaPayload($request),
            );
        }

        if ($user instanceof User && ($user->isPatient() || $user->isFamilyMember())) {
            $publicKey = config('webpush.vapid.public_key');

            $shared['webpush'] = [
                'publicKey' => is_string($publicKey) && $publicKey !== '' ? $publicKey : null,
                'subscribed' => $user->pushSubscriptions()
                    ->where('endpoint', 'not like', '%push.example.test%')
                    ->exists(),
            ];
        }

        if ($user instanceof User && $user->isPatient()) {
            $shared['patient_navigation'] = Inertia::defer(
                fn (): array => PatientNavigationState::inertiaPayload($request),
            );
        }

        return $shared;
    }
}
