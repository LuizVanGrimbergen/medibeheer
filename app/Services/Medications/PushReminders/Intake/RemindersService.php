<?php

declare(strict_types=1);

namespace App\Services\Medications\PushReminders\Intake;

use App\Models\Patient;
use App\Models\User;
use App\Notifications\Medications\PushReminders\Intake\DueNotification;
use App\Notifications\Medications\PushReminders\Intake\MissedNotification;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\PushReminders\Intake\ReminderCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

final class RemindersService
{
    public function __construct(
        private readonly CandidatesQuery $dueCandidates,
        private readonly ReminderCache $reminderCache,
    ) {}

    public function dueReminderSlotsForPatient(Patient $patient): array
    {
        return $this->dueCandidates->dueSlotsForPatient($patient);
    }

    public function sendDueReminders(): int
    {
        return $this->sendReminders(
            fn (int $patientId, int $scheduleId, string $doseTime, string $dateKey): string => $this->reminderCache->dueCacheKey(
                $patientId,
                $scheduleId,
                $doseTime,
                $dateKey,
            ),
            function (User $user, array $slot): void {
                Notification::send($user, new DueNotification($slot));
            },
            $this->dueCandidates->eachDueNow(...),
        );
    }

    public function sendMissedReminders(): int
    {
        return $this->sendReminders(
            fn (int $patientId, int $scheduleId, string $doseTime, string $dateKey): string => $this->reminderCache->missedCacheKey(
                $patientId,
                $scheduleId,
                $doseTime,
                $dateKey,
            ),
            function (User $user, array $slot): void {
                Notification::send($user, new MissedNotification($slot));
            },
            $this->dueCandidates->eachMissedNow(...),
        );
    }

    private function sendReminders(
        callable $cacheKeyFor,
        callable $notify,
        callable $eachCandidate,
    ): int {
        $sentCount = 0;
        $todayKey = MedicationIntakeClock::today()->toDateString();

        $eachCandidate(function (
            User $user,
            Patient $patient,
            array $slot,
        ) use (&$sentCount, $todayKey, $cacheKeyFor, $notify): void {
            $cacheKey = $cacheKeyFor(
                (int) $patient->id,
                (int) $slot['medication_schedule_id'],
                (string) $slot['dose_time'],
                $todayKey,
            );

            if (! Cache::add($cacheKey, true, $this->reminderCache->ttlUntilEndOfDay())) {
                return;
            }

            $notify($user, $slot);
            $sentCount++;
        });

        return $sentCount;
    }
}
