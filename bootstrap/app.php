<?php

use App\Exceptions\RegisterForbiddenAccessLogging;
use App\Http\Middleware\EnsureDoctor;
use App\Http\Middleware\EnsureFamilyMember;
use App\Http\Middleware\EnsurePatient;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('privacy:purge-expired-data')->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'patient' => EnsurePatient::class,
            'family_member' => EnsureFamilyMember::class,
            'doctor' => EnsureDoctor::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            AuthenticateSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        RegisterForbiddenAccessLogging::register($exceptions);

        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request): ?Response {
            if (! $request->header('X-Inertia')) {
                return null;
            }

            $seconds = (int) ($exception->getHeaders()['Retry-After'] ?? 0);
            $message = $seconds > 0
                ? trans('auth.throttle', ['seconds' => $seconds])
                : trans('auth.too_many_requests');

            return back(status: 303)
                ->with('error', $message)
                ->with('rate_limit_seconds', $seconds > 0 ? $seconds : null);
        });
    })->create();
