<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    private ?User $verifiedUser = null;

    public function authorize(): bool
    {
        $user = $this->verifiedUser();

        if ($user === null) {
            return false;
        }

        return hash_equals(
            sha1($user->getEmailForVerification()),
            (string) $this->route('hash'),
        );
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [];
    }

    public function verifiedUser(): ?User
    {
        if ($this->verifiedUser !== null) {
            return $this->verifiedUser;
        }

        $id = $this->route('id');

        if (! is_numeric($id)) {
            return null;
        }

        $this->verifiedUser = User::query()->find($id);

        return $this->verifiedUser;
    }
}
