<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Enums\UserRole;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\Patient;
use App\Models\User;
use App\Support\Medications\MedicationDoseTimeBlindIndex;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationIntakeReminderTiming;
use App\Support\Medications\MedicationScheduleDoseTimes;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final class PatientMedicationDueReminderCandidatesQuery
{
    public function __construct(
        private readonly MedicationScheduleOccursOnDate $scheduleOccursOnDate,
        private readonly PatientScheduledIntakeSlotBuilder $slotBuilder,
    ) {}

    public function eachDueNow(callable $onDue, ?CarbonInterface $now = null): void
    {
        $this->eachReminderNow(
            $onDue,
            static fn (string $doseTime, MedicationSchedule $schedule, CarbonInterface $now): bool => MedicationIntakeReminderTiming::isExactDoseTimeMinute($doseTime, $now),
            $now,
        );
    }

    public function eachMissedNow(callable $onMissed, ?CarbonInterface $now = null): void
    {
        $this->eachReminderNow(
            $onMissed,
            function (string $doseTime, MedicationSchedule $schedule, CarbonInterface $now): bool {
                $snoozeMinutes = MedicationScheduleDoseTimes::snoozeMinutesFor(
                    $doseTime,
                    (string) $schedule->dose_time,
                    $schedule->snooze_time,
                );

                return MedicationIntakeReminderTiming::isExactSnoozeEndMinute($doseTime, $snoozeMinutes, $now);
            },
            $now,
        );
    }

    private function eachReminderNow(
        callable $onReminder,
        callable $matchesMinute,
        ?CarbonInterface $now = null,
    ): void {
        $now = $this->resolveNow($now);
        $today = MedicationIntakeClock::today();

        $this->eachMatchingSchedule(null, $today, $now, function (
            User $user,
            Patient $patient,
            Medication $medication,
            MedicationSchedule $schedule,
            string $doseTime,
        ) use ($onReminder): void {
            $onReminder($user, $patient, $this->slotBuilder->buildReminderSlot($medication, $schedule, $doseTime));
        }, $matchesMinute);
    }

    public function dueSlotsForPatient(Patient $patient, ?CarbonInterface $now = null): array
    {
        $now = $this->resolveNow($now);
        $today = MedicationIntakeClock::today();
        $pending = [];

        $this->eachMatchingSchedule(
            $patient,
            $today,
            $now,
            function (
                User $_user,
                Patient $_patient,
                Medication $medication,
                MedicationSchedule $schedule,
                string $doseTime,
            ) use (&$pending): void {
                unset($_user, $_patient);

                $pending[] = [$medication, $schedule, $doseTime];
            },
            static fn (string $doseTime, MedicationSchedule $schedule, CarbonInterface $now): bool => MedicationIntakeReminderTiming::isExactDoseTimeMinute($doseTime, $now),
        );

        if ($pending === []) {
            return [];
        }

        $medications = (new EloquentCollection(
            array_map(static fn (array $row): Medication => $row[0], $pending),
        ))->unique('id')->values();

        $medications->load(['stocks', 'schedules.weekdays']);

        $intakes = $this->loadIntakesByKeyForPatient($patient, $today);
        $supplyEstimates = $this->slotBuilder->buildSupplyEstimates($medications, $today);

        $slots = [];

        foreach ($pending as [$medication, $schedule, $doseTime]) {
            $slots[] = $this->slotBuilder->buildIntakePayload(
                $medication,
                $schedule,
                $doseTime,
                $intakes,
                $supplyEstimates,
            );
        }

        return $slots;
    }

    private function eachMatchingSchedule(
        ?Patient $onlyPatient,
        CarbonImmutable $today,
        CarbonInterface $now,
        callable $onMatch,
        callable $matchesMinute,
    ): void {
        $todayKey = $today->toDateString();

        $this->activeSchedulesWithPushQuery($todayKey, $onlyPatient)
            ->with(['medication', 'medication.patient.user', 'weekdays'])
            ->chunkById(100, function (EloquentCollection $schedules) use ($today, $now, $onMatch, $todayKey, $matchesMinute): void {
                $takenKeys = $this->takenIntakeKeysForSchedules($schedules, $todayKey);

                foreach ($schedules as $schedule) {
                    $this->matchSchedule($schedule, $today, $now, $takenKeys, $onMatch, $matchesMinute);
                }
            });
    }

    private function matchSchedule(
        MedicationSchedule $schedule,
        CarbonImmutable $today,
        CarbonInterface $now,
        array $takenKeys,
        callable $onMatch,
        callable $matchesMinute,
    ): void {
        if (! $this->scheduleOccursOnDate->isIntakeDueOn($schedule, $today)) {
            return;
        }

        $medication = $schedule->medication;
        $patient = $medication?->patient;
        $user = $patient?->user;

        if ($medication === null || $patient === null || $user === null) {
            return;
        }

        foreach ($this->scheduleOccursOnDate->sortedDoseTimes($schedule) as $doseTime) {
            if ($doseTime === '') {
                continue;
            }

            if (! $matchesMinute($doseTime, $schedule, $now)) {
                continue;
            }

            $takenKey = $schedule->id.':'.MedicationDoseTimeBlindIndex::hash($doseTime);

            if (isset($takenKeys[$takenKey])) {
                continue;
            }

            $onMatch($user, $patient, $medication, $schedule, $doseTime);
        }
    }

    private function activeSchedulesWithPushQuery(string $todayKey, ?Patient $onlyPatient): Builder
    {
        return MedicationSchedule::query()
            ->whereDate('start_date', '<=', $todayKey, 'and')
            ->where(function (Builder $schedule) use ($todayKey): void {
                $schedule->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $todayKey, 'and');
            })
            ->whereHas('medication', function (Builder $medication) use ($onlyPatient): void {
                $medication->whereHas('patient', function (Builder $patient) use ($onlyPatient): void {
                    if ($onlyPatient !== null) {
                        $patient->whereKey($onlyPatient->id);
                    }

                    $patient->whereHas('user', function (Builder $user): void {
                        $user->where('role', UserRole::PATIENT)
                            ->whereHas('pushSubscriptions', function (Builder $subscriptions): void {
                                $subscriptions->where('endpoint', 'not like', '%push.example.test%');
                            });
                    });
                });
            });
    }

    private function takenIntakeKeysForSchedules(EloquentCollection $schedules, string $todayKey): array
    {
        $scheduleIds = $schedules->pluck('id')->all();

        if ($scheduleIds === []) {
            return [];
        }

        $keys = [];

        MedicationIntake::query()
            ->whereIn('medication_schedule_id', $scheduleIds, 'and', false)
            ->whereDate('intake_date', '=', $todayKey, 'and')
            ->whereNotNull('taken_at', 'and')
            ->get(['medication_schedule_id', 'dose_time_index'])
            ->each(function (MedicationIntake $intake) use (&$keys): void {
                $keys[$intake->medication_schedule_id.':'.$intake->dose_time_index] = true;
            });

        return $keys;
    }

    private function loadIntakesByKeyForPatient(Patient $patient, CarbonImmutable $today): Collection
    {
        return MedicationIntake::query()
            ->where('patient_id', $patient->id)
            ->whereDate('intake_date', '=', $today->toDateString(), 'and')
            ->get()
            ->keyBy(fn (MedicationIntake $intake): string => $this->slotBuilder->intakeKey(
                $intake->medication_schedule_id,
                (string) $intake->dose_time,
            ));
    }

    private function resolveNow(?CarbonInterface $now): CarbonImmutable
    {
        if ($now === null) {
            return MedicationIntakeClock::now();
        }

        return CarbonImmutable::parse($now);
    }
}
