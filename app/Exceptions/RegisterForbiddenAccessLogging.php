<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\SecurityActivityDescription;
use App\Providers\RegisterGateAccessDeniedLogging;
use App\Services\Audit\SecurityActivityLogger;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class RegisterForbiddenAccessLogging
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->renderable(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 403) {
                return null;
            }

            if ($request->attributes->get(RegisterGateAccessDeniedLogging::REQUEST_FLAG) === true) {
                return null;
            }

            app(SecurityActivityLogger::class)->recordAccessDenied(
                $request,
                SecurityActivityDescription::ACCESS_FORBIDDEN,
                $exception,
            );

            return null;
        });
    }
}
