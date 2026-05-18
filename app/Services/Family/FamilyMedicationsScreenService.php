<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\Patient;
use App\Services\Medications\PatientScheduledIntakesQuery;
use App\Services\Patient\PatientMedicationsScreenService;
use App\Support\InertiaPagination;

final class FamilyMedicationsScreenService
{
    public function __construct(
        private readonly PatientMedicationsScreenService $patientMedicationsScreenService,
        private readonly PatientScheduledIntakesQuery $scheduledIntakesQuery,
    ) {}

    public function buildProps(Patient $patient, string $calendarMonth): array
    {
        $calendar = $this->scheduledIntakesQuery->monthCalendarDataForPatient($patient, $calendarMonth);

        return [
            ...$this->patientMedicationsScreenService->buildProps($patient),
            'medication_calendar_month' => $calendarMonth,
            'medication_calendar_days' => $calendar['days'],
            'medication_calendar_slots' => $calendar['slots'],
        ];
    }

    public function emptyProps(string $calendarMonth): array
    {
        return [
            'medications' => InertiaPagination::empty(),
            'medication_calendar_month' => $calendarMonth,
            'medication_calendar_days' => [],
            'medication_calendar_slots' => [],
        ];
    }
}
