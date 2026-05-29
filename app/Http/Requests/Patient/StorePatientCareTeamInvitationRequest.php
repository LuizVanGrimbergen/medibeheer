<?php

namespace App\Http\Requests\Patient;

use App\Enums\UserRole;
use App\Models\User;
use App\Rules\UserEmailHasRole;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class StorePatientCareTeamInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isPatient();
    }

    abstract protected function inviteeRole(): UserRole;

    abstract protected function translationKey(): string;

    public function rules(): array
    {
        $translationKey = $this->translationKey();

        return [
            'email' => [
                'required',
                'email',
                'max:255',
                new UserEmailHasRole($this->inviteeRole(), "{$translationKey}.validation.wrong_role"),
                function (string $attribute, mixed $value, Closure $fail, Validator $_) use ($translationKey): void {
                    if ($attribute === '') {
                        return;
                    }

                    $normalized = User::normalizeEmail((string) $value);
                    $self = User::normalizeEmail($this->user()->email);

                    if ($normalized === $self) {
                        $fail(trans("{$translationKey}.validation.not_self"));
                    }
                },
            ],
        ];
    }
}
