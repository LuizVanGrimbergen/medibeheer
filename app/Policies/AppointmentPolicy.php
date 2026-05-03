<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\Patient;
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

        return $this->familyLinkedToPatient($user, $appointment->patient);
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

    private function familyLinkedToPatient(User $user, Patient $patient): bool
    {
        if (! $user->isFamilyMember()) {
            return false;
        }

        $family = $user->family;

        if ($family === null) {
            return false;
        }

        if ($family->patient_id === null) {
            return false;
        }

        return $family->patient_id === $patient->getKey();
    }
}
