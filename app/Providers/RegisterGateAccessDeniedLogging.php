<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\SecurityActivityDescription;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class RegisterGateAccessDeniedLogging
{
    public const string REQUEST_FLAG = 'audit.authorization_denied_logged';

    public static function register(): void
    {
        Gate::after(function (
            mixed $user,
            string $ability,
            bool|Response $result,
            mixed $arguments,
        ): void {
            $denied = $result instanceof Response
                ? $result->denied()
                : $result === false;

            if (! $denied) {
                return;
            }

            $request = request();

            if (! $request instanceof Request) {
                return;
            }

            if ($request->attributes->get(self::REQUEST_FLAG) === true) {
                return;
            }

            $request->attributes->set(self::REQUEST_FLAG, true);

            $subject = self::resolveSubject($arguments);

            $properties = [
                'ability' => $ability,
            ];

            if ($user instanceof User) {
                $properties['public_id'] = $user->public_id;
                $properties['role'] = $user->role->value;
            }

            app(SecurityActivityLogger::class)->record(
                SecurityActivityDescription::AUTHORIZATION_DENIED,
                causer: $user instanceof User ? $user : null,
                subject: $subject,
                properties: $properties,
            );
        });
    }

    private static function resolveSubject(mixed $arguments): ?Model
    {
        if (! is_array($arguments)) {
            return null;
        }

        foreach ($arguments as $argument) {
            if ($argument instanceof Model) {
                return $argument;
            }
        }

        return null;
    }
}
