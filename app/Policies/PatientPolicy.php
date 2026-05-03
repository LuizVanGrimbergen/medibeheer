<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(): bool
    {
        return false;
    }

    public function view(User $user, Patient $patient): bool
    {
        if ($user->id === $patient->user_id) {
            return true;
        }

        if (! $user->isDoctor()) {
            return false;
        }

        $doctor = $user->doctor;

        if ($doctor === null) {
            return false;
        }

        return $doctor->patients()->whereKey($patient->getKey())->exists();
    }

    public function create(): bool
    {
        return false;
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->id === $patient->user_id;
    }

    public function delete(): bool
    {
        return false;
    }
}
