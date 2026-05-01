<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
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
                'rateLimitSeconds' => fn () => $request->session()->get('rate_limit_seconds'),
            ],
        ];
    }
}
