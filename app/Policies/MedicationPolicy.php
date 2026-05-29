<?php

namespace App\Policies;

use App\Enums\MedicationListStatus;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\User;
use App\Support\Medications\MedicationListClassifier;

class MedicationPolicy
{
    public function create(User $user): bool
    {
        return $user->isPatient();
    }

    public function view(User $user, Medication $medication): bool
    {
        return $this->canAccessPatientMedicationData($user, $medication->patient);
    }

    public function update(User $user, Medication $medication): bool
    {
        return $this->canMutateActiveMedication($user, $medication);
    }

    public function updateStock(User $user, Medication $medication): bool
    {
        if ($this->ownsPatientMedication($user, $medication)) {
            return true;
        }

        return $user->isFamilyLinkedToPatient($medication->patient);
    }

    public function delete(User $user, Medication $medication): bool
    {
        return $this->canMutateActiveMedication($user, $medication);
    }

    private function canMutateActiveMedication(User $user, Medication $medication): bool
    {
        if (! $this->ownsPatientMedication($user, $medication)) {
            return false;
        }

        return app(MedicationListClassifier::class)->statusFor($medication) === MedicationListStatus::ACTIVE;
    }

    private function ownsPatientMedication(User $user, Medication $medication): bool
    {
        if (! $user->isPatient()) {
            return false;
        }

        $patient = $user->patient;

        if ($patient === null) {
            return false;
        }

        return $patient->is($medication->patient);
    }

    private function canAccessPatientMedicationData(User $user, Patient $patient): bool
    {
        if ($user->isPatient() && $user->patient?->is($patient)) {
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
}
