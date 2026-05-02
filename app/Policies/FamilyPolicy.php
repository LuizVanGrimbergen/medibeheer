<?php

namespace App\Policies;

use App\Models\Family;
use App\Models\User;

class FamilyPolicy
{
    public function viewAny(): bool
    {
        return false;
    }

    public function view(User $user, Family $family): bool
    {
        return $user->id === $family->user_id;
    }

    public function create(): bool
    {
        return false;
    }

    public function update(User $user, Family $family): bool
    {
        return $user->id === $family->user_id;
    }

    public function delete(): bool
    {
        return false;
    }
}
