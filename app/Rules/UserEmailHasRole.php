<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\UserRole;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class UserEmailHasRole implements ValidationRule
{
    public function __construct(
        private readonly UserRole $role,
        private readonly string $messageKey,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($attribute === '') {
            return;
        }

        $existingUser = User::findByEmail(User::normalizeEmail((string) $value));

        if ($existingUser === null) {
            return;
        }

        if ($existingUser->role !== $this->role) {
            $fail(trans($this->messageKey));
        }
    }
}
