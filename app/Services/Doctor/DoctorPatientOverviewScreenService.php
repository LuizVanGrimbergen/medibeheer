<?php

declare(strict_types=1);

namespace App\Services\Doctor;

use App\Models\Patient;
use App\Services\Family\FamilyWellbeingScreenService;
use App\Services\Medications\PatientCriticalPrescriptionsQuery;
use App\Services\Medications\PatientScheduledIntakesQuery;

final class DoctorPatientOverviewScreenService
{
    public function __construct(
        private readonly PatientScheduledIntakesQuery $scheduledIntakesQuery,
        private readonly FamilyWellbeingScreenService $wellbeingScreenService,
        private readonly PatientCriticalPrescriptionsQuery $criticalPrescriptionsQuery,
    ) {}

    /** @return array<string, mixed> */
    public function buildProps(Patient $patient, string $calendarMonth): array
    {
        $patient->loadMissing('user');

        $medicationCalendar = $this->scheduledIntakesQuery->monthCalendarDataForPatient($patient, $calendarMonth);
        $wellbeing = $this->wellbeingScreenService->buildProps($calendarMonth, $patient);

        return [
            'selected_patient' => [
                'public_id' => $patient->user->public_id,
                'name' => $patient->user->name,
            ],
            'medication_calendar_month' => $calendarMonth,
            'medication_calendar_days' => $medicationCalendar['days'],
            'medication_calendar_slots' => $medicationCalendar['slots'],
            'wellbeing_calendar_month' => $wellbeing['wellbeing_calendar_month'],
            'wellbeing_calendar_checkins' => $wellbeing['wellbeing_calendar_checkins'],
            'wellbeing_checkins' => $wellbeing['wellbeing_checkins'],
            'urgent_prescriptions' => $this->criticalPrescriptionsQuery->forPatient($patient),
        ];
    }
}
