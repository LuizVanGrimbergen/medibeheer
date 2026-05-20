<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\Patient;
use App\Services\Medications\PatientScheduledIntakesQuery;

final class FamilyMedicationIntakeListService
{
    public function __construct(
        private PatientScheduledIntakesQuery $scheduledIntakes,
    ) {}

    public function takenWithinDaysForPatient(Patient $patient, int $days): array
    {
        return $this->scheduledIntakes->takenSlotsWithinDaysForPatient($patient, $days);
    }
}
