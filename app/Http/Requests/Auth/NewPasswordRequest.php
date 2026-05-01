<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as PasswordRule;

class NewPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'size:64', 'regex:/^[a-f0-9]{64}$/'],
            'password' => ['required', 'string', 'max:255', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()->symbols()->uncompromised()],
        ];
    }
}
