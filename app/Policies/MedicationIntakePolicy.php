<?php

namespace App\Policies;

use App\Models\MedicationIntake;
use App\Models\User;

class MedicationIntakePolicy
{
    public function create(User $user): bool
    {
        return $user->isPatient();
    }

    public function view(User $user, MedicationIntake $medicationIntake): bool
    {
        return $user->can('view', $medicationIntake->medication);
    }
}
