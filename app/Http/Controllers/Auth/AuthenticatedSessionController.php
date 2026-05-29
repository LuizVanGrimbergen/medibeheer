<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SecurityActivityDescription;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly SecurityActivityLogger $securityActivityLogger,
    ) {}

    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Display the login view.
     */
    public function create(Request $request, ResolveSelectedRole $resolveSelectedRole): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'selectedRole' => $resolveSelectedRole($request),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|SymfonyResponse
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->authenticate();

        $request->session()->regenerate();

        $authenticatedUser = $request->user();

        if ($authenticatedUser === null) {
            return redirect()->route('login');
        }

        $redirectUrl = $this->resolvePostAuthenticationRedirectUrl($request, $authenticatedUser);

        if ($request->header('X-Inertia')) {
            return Inertia::location($redirectUrl);
        }

        return redirect()->to($redirectUrl);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser !== null) {
            $this->securityActivityLogger->record(
                SecurityActivityDescription::AUTH_LOGOUT,
                causer: $authenticatedUser,
                properties: [
                    'public_id' => $authenticatedUser->public_id,
                ],
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function resolvePostAuthenticationRedirectUrl(Request $request, User $user): string
    {
        if (! $user->hasVerifiedEmail()) {
            return route('verification.notice', absolute: false);
        }

        $default = $user->defaultAuthenticatedHomeUrl();
        $intended = $request->session()->pull('url.intended');

        if ($intended !== null && ! $this->isAuthenticationRedirectUrl($intended)) {
            return $intended;
        }

        return $default;
    }

    private function isAuthenticationRedirectUrl(string $url): bool
    {
        $path = '/'.ltrim((string) parse_url($url, PHP_URL_PATH), '/');

        $authenticationPaths = collect([
            route('login', absolute: false),
            route('register', absolute: false),
            route('verification.notice', absolute: false),
            route('password.request', absolute: false),
            route('password.confirm', absolute: false),
        ])
            ->map(static fn (string $authenticationRoute): string => '/'.ltrim((string) parse_url($authenticationRoute, PHP_URL_PATH), '/'))
            ->all();

        if (in_array($path, $authenticationPaths, true)) {
            return true;
        }

        return str_starts_with($path, '/reset-password');
    }
}
