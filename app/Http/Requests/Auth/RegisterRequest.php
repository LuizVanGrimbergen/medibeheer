<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255',
                // check if email is unique
                function (string $attribute, mixed $value, Closure $fail, Validator $_): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $emailHashExists = User::query()
                        ->whereIn('email_hash', User::emailHashCandidates($value), 'and', false)
                        ->exists();

                    if ($emailHashExists) {
                        $fail(trans('validation.unique', ['attribute' => $attribute]));
                    }
                },
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()],
            'password_confirmation' => ['required', 'string'],
            'accepted_privacy_policy' => ['required', 'accepted'],
            'accepted_health_data_processing' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'accepted_privacy_policy.accepted' => trans('privacy.consent.privacy_policy'),
            'accepted_health_data_processing.accepted' => trans('privacy.consent.health_data'),
        ];
    }

    // normalize email
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => User::normalizeEmail($this->string('email')->toString()),
        ]);
    }
}
