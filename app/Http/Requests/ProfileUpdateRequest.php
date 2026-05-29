<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProfileUpdateRequest extends FormRequest
{
    /**************************************/
    /*           Authorization */
    /**************************************/

    public function authorize(): bool
    {
        return true;
    }

    /**************************************/
    /*          Validation Rules */
    /**************************************/

    public function rules(): array
    {
        $email = $this->string('email')->toString();
        $emailChanged = $this->user()?->email !== $email;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail, Validator $_): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $emailHashExists = User::query()
                        ->whereIn('email_hash', User::emailHashCandidates($value), 'and', false)
                        ->where('id', '!=', $this->user()->id)
                        ->exists();

                    if ($emailHashExists) {
                        $fail(trans('validation.unique', ['attribute' => $attribute]));
                    }
                },
            ],
            'current_password' => Rule::when(
                $emailChanged,
                ['required', 'current_password'],
                ['nullable'],
            ),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => User::normalizeEmail($this->string('email')->toString()),
        ]);
    }
}
