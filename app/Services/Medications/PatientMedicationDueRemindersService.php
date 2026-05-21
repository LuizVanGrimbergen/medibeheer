<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Models\Patient;
use App\Models\User;
use App\Notifications\Patient\MedicationIntakeDueNotification;
use App\Notifications\Patient\MedicationIntakeMissedNotification;
use App\Support\Medications\MedicationIntakeClock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

final class PatientMedicationDueRemindersService
{
    private const string DUE_CACHE_KEY_PREFIX = 'patient-medication-due-reminder';

    private const string MISSED_CACHE_KEY_PREFIX = 'patient-medication-missed-reminder';

    public function __construct(
        private readonly PatientMedicationDueReminderCandidatesQuery $dueCandidates,
    ) {}

    public function dueReminderSlotsForPatient(Patient $patient): array
    {
        return $this->dueCandidates->dueSlotsForPatient($patient);
    }

    public function sendDueReminders(): int
    {
        return $this->sendReminders(
            self::DUE_CACHE_KEY_PREFIX,
            fn (User $user, array $slot): mixed => Notification::send($user, new MedicationIntakeDueNotification($slot)),
            fn (callable $onReminder) => $this->dueCandidates->eachDueNow($onReminder),
        );
    }

    public function sendMissedReminders(): int
    {
        return $this->sendReminders(
            self::MISSED_CACHE_KEY_PREFIX,
            fn (User $user, array $slot): mixed => Notification::send($user, new MedicationIntakeMissedNotification($slot)),
            fn (callable $onReminder) => $this->dueCandidates->eachMissedNow($onReminder),
        );
    }

    private function sendReminders(
        string $cacheKeyPrefix,
        callable $notify,
        callable $eachCandidate,
    ): int {
        $sentCount = 0;
        $todayKey = MedicationIntakeClock::today()->toDateString();

        $eachCandidate(function (
            User $user,
            Patient $patient,
            array $slot,
        ) use (&$sentCount, $todayKey, $cacheKeyPrefix, $notify): void {
            $cacheKey = $this->reminderCacheKey(
                $cacheKeyPrefix,
                $patient->id,
                (int) $slot['medication_schedule_id'],
                (string) $slot['dose_time'],
                $todayKey,
            );

            if (! Cache::add($cacheKey, true, MedicationIntakeClock::today()->endOfDay())) {
                return;
            }

            $notify($user, $slot);
            $sentCount++;
        });

        return $sentCount;
    }

    private function reminderCacheKey(
        string $prefix,
        int $patientId,
        int $scheduleId,
        string $doseTime,
        string $dateKey,
    ): string {
        return "{$prefix}:{$patientId}:{$scheduleId}:{$doseTime}:{$dateKey}";
    }
}
