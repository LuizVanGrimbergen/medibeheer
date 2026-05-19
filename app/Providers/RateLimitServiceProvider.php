<?php

namespace App\Providers;

use App\Support\RateLimiting\AuthRateLimits;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(AuthRateLimits::LOGIN_MAX_ATTEMPTS)
                ->by(AuthRateLimits::loginKey($request));
        });

        RateLimiter::for('forgot-password', function (Request $request): Limit {
            return Limit::perMinute(AuthRateLimits::FORGOT_PASSWORD_MAX_ATTEMPTS)
                ->by(AuthRateLimits::forgotPasswordKey($request));
        });

        RateLimiter::for('register', function (Request $request): Limit {
            return Limit::perMinute(5)->by((string) $request->ip());
        });

        RateLimiter::for('reset-password', function (Request $request): Limit {
            return Limit::perMinute(3)->by((string) $request->ip());
        });

        RateLimiter::for('email-verification', function (Request $request): Limit {
            return Limit::perMinute(6)->by(self::userOrIpKey($request));
        });

        RateLimiter::for('account-delete', function (Request $request): Limit {
            return Limit::perMinute(5)->by(self::userOrIpKey($request));
        });

        RateLimiter::for('data-export', function (Request $request): Limit {
            return Limit::perMinute(3)->by(self::userOrIpKey($request));
        });

        RateLimiter::for('confirm-password', $this->passwordActionRateLimiter());
        RateLimiter::for('update-password', $this->passwordActionRateLimiter());

        RateLimiter::for('authenticated-area', function (Request $request): Limit {
            return Limit::perMinute(120)->by(self::userOrIpKey($request));
        });

        RateLimiter::for('family-invitation-accept', function (Request $request): Limit {
            return Limit::perMinute(10)->by(self::userOrIpKey($request));
        });

        RateLimiter::for('family-transport-invitation', function (Request $request): Limit {
            return Limit::perMinute(10)->by(self::userOrIpKey($request));
        });

        RateLimiter::for('family-active-patient-switch', function (Request $request): Limit {
            return Limit::perMinute(30)->by(self::userOrIpKey($request));
        });

        RateLimiter::for('family-invitation-send', function (Request $request): Limit {
            return Limit::perMinute(5)->by(self::userOrIpKey($request));
        });

        RateLimiter::for('family-invitation-revoke', function (Request $request): Limit {
            return Limit::perMinute(30)->by(self::userOrIpKey($request));
        });
    }

    private static function userOrIpKey(Request $request): string
    {
        $user = $request->user();

        if ($user !== null) {
            return "user:{$user->id}";
        }

        return (string) $request->ip();
    }

    private function passwordActionRateLimiter(): \Closure
    {
        return function (Request $request): Limit {
            return Limit::perMinute(5)->by(self::userOrIpKey($request));
        };
    }
}
