<?php

namespace App\Http\Requests\Patient;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreFamilyInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isPatient();
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255',
                function (mixed $value, Closure $fail): void {
                    $normalized = User::normalizeEmail((string) $value);
                    $self = User::normalizeEmail($this->user()->email);

                    if ($normalized === $self) {
                        $fail(trans('family_invitation.validation.not_self'));
                    }
                },
            ],
        ];
    }
}
