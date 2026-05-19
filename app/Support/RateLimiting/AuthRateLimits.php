<?php

declare(strict_types=1);

namespace App\Support\RateLimiting;

use App\Models\User;
use Illuminate\Http\Request;

final class AuthRateLimits
{
    public const int LOGIN_MAX_ATTEMPTS = 5;

    public const int LOGIN_DECAY_SECONDS = 60;

    public const int FORGOT_PASSWORD_MAX_ATTEMPTS = 3;

    public const int FORGOT_PASSWORD_DECAY_SECONDS = 60;

    public static function loginKey(Request $request): string
    {
        $email = User::normalizeEmail($request->string('email')->toString());

        return User::hashEmail($email).'|'.$request->ip();
    }

    public static function forgotPasswordKey(Request $request): string
    {
        return self::loginKey($request);
    }
}
