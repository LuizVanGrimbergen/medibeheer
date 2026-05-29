<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function create(User $user): bool
    {
        return $user->isPatient();
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($this->ownsPatient($user, $appointment)) {
            return true;
        }

        return $user->isFamilyLinkedToPatient($appointment->patient);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $this->ownsPatient($user, $appointment);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $this->ownsPatient($user, $appointment);
    }

    private function ownsPatient(User $user, Appointment $appointment): bool
    {
        if (! $user->isPatient()) {
            return false;
        }

        $patient = $user->patient;

        if ($patient === null) {
            return false;
        }

        return $patient->is($appointment->patient);
    }
}
