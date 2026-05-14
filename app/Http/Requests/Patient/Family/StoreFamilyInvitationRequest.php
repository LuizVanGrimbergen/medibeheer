<?php

namespace App\Http\Requests\Patient\Family;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFamilyInvitationRequest extends FormRequest
{
    /**************************************/
    /*           Authorization */
    /**************************************/
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isPatient();
    }

    /**************************************/
    /*          Validation Rules */
    /**************************************/
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255',
                function (string $attribute, mixed $value, Closure $fail, Validator $_): void {
                    if ($attribute === '') {
                        return;
                    }

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
