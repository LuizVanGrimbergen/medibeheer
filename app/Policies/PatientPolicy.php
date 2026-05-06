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
        if ($this->patientProfileOwnedByUser($user, $patient)) {
            return true;
        }

        if ($user->isFamilyLinkedToPatient($patient)) {
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
        return $this->patientProfileOwnedByUser($user, $patient);
    }

    public function delete(): bool
    {
        return false;
    }

    private function patientProfileOwnedByUser(User $user, Patient $patient): bool
    {
        return (int) $user->getAuthIdentifier() === (int) $patient->user_id;
    }
}
