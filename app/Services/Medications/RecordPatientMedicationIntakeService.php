<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Events\Family\MedicationIntakeRecordedEvent;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\Patient;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationScheduleDoseTimes;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class RecordPatientMedicationIntakeService
{
    public function __construct(
        private readonly MedicationScheduleOccursOnDate $scheduleOccursOnDate,
    ) {}

    public function recordTodayForSchedule(
        Patient $patient,
        MedicationSchedule $schedule,
        string $doseTime,
        bool $allowOutsideIntakeWindow = false,
        ?CarbonImmutable $takenAt = null,
    ): MedicationIntake {
        if ($schedule->patient_id !== $patient->id) {
            throw ValidationException::withMessages([
                'medication_schedule_id' => trans('validation.exists', [
                    'attribute' => 'medication_schedule_id',
                ]),
            ]);
        }

        $trimmedDoseTime = trim($doseTime);
        $today = MedicationIntakeClock::today();

        if (! $this->scheduleOccursOnDate->hasScheduledDoseOn($schedule, $trimmedDoseTime, $today)) {
            throw ValidationException::withMessages([
                'dose_time' => trans('medication.intake_slot_not_due_today'),
            ]);
        }

        if (! $allowOutsideIntakeWindow && ! MedicationScheduleDoseTimes::isWithinIntakeWindow(
            $trimmedDoseTime,
            (string) $schedule->dose_time,
            $schedule->snooze_time,
        )) {
            throw ValidationException::withMessages([
                'dose_time' => trans('medication.intake_outside_window'),
            ]);
        }

        $intake = MedicationIntake::firstOrNewForScheduleDateAndDoseTime(
            $schedule->id,
            $today->toDateString(),
            $trimmedDoseTime,
        );

        $intake->fill([
            'patient_id' => $patient->id,
            'medication_id' => $schedule->medication_id,
            'taken_at' => $takenAt ?? MedicationIntakeClock::now(),
        ]);
        $intake->save();

        MedicationIntakeRecordedEvent::dispatch($intake);

        return $intake;
    }
}
