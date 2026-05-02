<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**************************************/
    /*             Abilities */
    /**************************************/

    public function viewAny(): bool
    {
        return false;
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->id === $patient->user_id;
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
