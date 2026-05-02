<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\User;

class DoctorPolicy
{
    public function viewAny(): bool
    {
        return false;
    }

    public function view(User $user, Doctor $doctor): bool
    {
        return $user->id === $doctor->user_id;
    }

    public function create(): bool
    {
        return false;
    }

    public function update(User $user, Doctor $doctor): bool
    {
        return $user->id === $doctor->user_id;
    }

    public function delete(): bool
    {
        return false;
    }
}
