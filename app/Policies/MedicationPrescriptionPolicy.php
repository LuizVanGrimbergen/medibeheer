<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MedicationPrescription;
use App\Models\User;

class MedicationPrescriptionPolicy
{
    public function update(User $user, MedicationPrescription $medicationPrescription): bool
    {
        $medication = $medicationPrescription->medication;

        if ($medication === null) {
            return false;
        }

        return $user->can('storePrescription', $medication);
    }
}
