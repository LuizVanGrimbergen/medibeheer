<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\Patient;
use App\Support\FamilyUpdatesPeriodDays;
use Illuminate\Http\Request;

final class FamilyUpdatesScreenService
{
    public function __construct(
        private FamilyDailyCheckinListService $checkinList,
        private FamilyMedicationIntakeListService $medicationIntakeList,
    ) {}

    public function buildProps(Request $request, Patient $patient): array
    {
        $periodDays = FamilyUpdatesPeriodDays::fromRequest($request);

        return [
            'updates_period_days' => $periodDays,
            'updates_checkins' => $this->checkinList->withinDaysForPatient($patient, $periodDays),
            'updates_medication_intakes' => $this->medicationIntakeList->takenWithinDaysForPatient($patient, $periodDays),
        ];
    }

    public function emptyProps(Request $request): array
    {
        return [
            'updates_period_days' => FamilyUpdatesPeriodDays::fromRequest($request),
            'updates_checkins' => [],
            'updates_medication_intakes' => [],
        ];
    }
}
