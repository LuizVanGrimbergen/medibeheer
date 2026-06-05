<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\Patient;

final class FamilyUpdatesScreenService
{
    private const UPDATES_LOOKBACK_DAYS = 1;

    public function __construct(
        private FamilyDailyCheckinListService $checkinList,
        private FamilyMedicationIntakeListService $medicationIntakeList,
    ) {}

    public function buildProps(Patient $patient): array
    {
        return [
            'updates_checkins' => $this->checkinList->withinDaysForPatient($patient, self::UPDATES_LOOKBACK_DAYS),
            'updates_medication_intakes' => $this->medicationIntakeList->takenWithinDaysForPatient($patient, self::UPDATES_LOOKBACK_DAYS),
        ];
    }

    public function emptyProps(): array
    {
        return [
            'updates_checkins' => [],
            'updates_medication_intakes' => [],
        ];
    }
}
