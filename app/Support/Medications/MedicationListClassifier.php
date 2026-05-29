<?php

declare(strict_types=1);

namespace App\Support\Medications;

use App\Enums\MedicationListStatus;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use Carbon\CarbonInterface;

final class MedicationListClassifier
{
    public function statusFor(Medication $medication): MedicationListStatus
    {
        if ($medication->trashed()) {
            return MedicationListStatus::REMOVED;
        }

        $schedule = $this->resolvePrimarySchedule($medication);
        $endDate = $schedule?->end_date;

        if ($endDate instanceof CarbonInterface && $endDate->toDateString() < MedicationIntakeClock::today()->toDateString()) {
            return MedicationListStatus::ENDED;
        }

        return MedicationListStatus::ACTIVE;
    }

    private function resolvePrimarySchedule(Medication $medication): ?MedicationSchedule
    {
        if ($medication->relationLoaded('schedules')) {
            return $medication->schedules->first();
        }

        return $medication->schedules()->first();
    }
}
