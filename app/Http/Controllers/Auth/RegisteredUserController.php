<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**************************************/
    /*              Actions */
    /**************************************/

    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Register', [
            'selectedRole' => $this->resolveSelectedRole($request),
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

        Auth::login($user);
        $request->session()->regenerate();

        Log::info('auth.registration.succeeded', [
            'public_id' => $user->public_id,
            'role' => $user->role->value,
        ]);

        return redirect()->route('verification.notice');
    }

    private function resolveSelectedRole(Request $request): ?string
    {
        $role = $request->query('role');

        if (! is_string($role)) {
            return null;
        }

        $userRole = UserRole::tryFrom($role);

        if ($userRole === null) {
            return null;
        }

        return $userRole->value;
    }
}
