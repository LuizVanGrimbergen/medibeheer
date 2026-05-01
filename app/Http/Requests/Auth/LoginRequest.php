<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const int MAX_LOGIN_ATTEMPTS = 5;

    private const int LOGIN_THROTTLE_DECAY_SECONDS = 60;

    private static ?string $dummyHash = null;

    /**************************************/
    /*           Authorization */
    /**************************************/

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**************************************/
    /*          Validation Rules */
    /**************************************/

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'lowercase', 'email:rfc'],
            'password' => ['required', 'string'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => User::normalizeEmail($this->string('email')->toString()),
        ]);
    }

    /**************************************/
    /*              Helpers */
    /**************************************/

    /**
     * Attempt to authenticate the request's credentials.
     */
    public function authenticate(): void
    {
        $throttleKey = $this->throttleKey();
        $this->ensureIsNotRateLimited($throttleKey);

        $requestedEmail = $this->emailInput();
        $user = User::findByEmail($requestedEmail);
        $requestedRole = $this->roleInput();
        $hashedPassword = $user?->password ?? self::dummyHash();
        $credentialsAreInvalid = ! Hash::check($this->passwordInput(), $hashedPassword)
            || $user === null
            || $user->role !== $requestedRole;

        if ($credentialsAreInvalid) {
            RateLimiter::hit($throttleKey, self::LOGIN_THROTTLE_DECAY_SECONDS);
            Log::warning('auth.login.failed', [
                'ip' => $this->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($throttleKey);
        Log::info('auth.login.succeeded', [
            'public_id' => $user->public_id,
            'ip' => $this->ip(),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureIsNotRateLimited(string $throttleKey): void
    {
        if (! RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($throttleKey);
        $this->session()->flash('rate_limit_seconds', $seconds);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $normalizedEmail = $this->emailInput();
        $emailHash = User::hashEmail($normalizedEmail);

        return $emailHash.'|'.$this->ip();
    }

    private function emailInput(): string
    {
        return $this->string('email')->toString();
    }

    private function passwordInput(): string
    {
        return $this->string('password')->toString();
    }

    private function roleInput(): UserRole
    {
        $role = UserRole::tryFrom($this->string('role')->toString());

        if ($role !== null) {
            return $role;
        }

        throw ValidationException::withMessages([
            'role' => trans('validation.enum', [
                'attribute' => 'role',
            ]),
        ]);
    }

    private static function dummyHash(): string
    {
        if (self::$dummyHash !== null) {
            return self::$dummyHash;
        }

        self::$dummyHash = Hash::make('auth-timing-protection');

        return self::$dummyHash;
    }
}
