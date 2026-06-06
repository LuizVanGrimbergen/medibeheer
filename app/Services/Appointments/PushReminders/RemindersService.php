<?php

declare(strict_types=1);

namespace App\Services\Appointments\PushReminders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Notifications\Appointments\PushReminders\AppointmentReminderNotification;
use App\Services\PushReminders\PushReminderDispatcher;
use App\Services\PushReminders\RecipientsResolver;
use App\Support\Appointments\PushReminders\AppointmentReminderKind;
use App\Support\Appointments\PushReminders\ReminderCache;

final class RemindersService
{
    public function __construct(
        private readonly CandidatesQuery $candidates,
        private readonly RecipientsResolver $recipientsResolver,
        private readonly ReminderCache $reminderCache,
        private readonly PushReminderDispatcher $dispatcher,
    ) {}

    public function sendTwoDaysBeforeReminders(): int
    {
        return $this->sendReminders(
            AppointmentReminderKind::TwoDaysBefore,
            $this->candidates->eachTwoDaysBefore(...),
        );
    }

    public function sendTwoHoursBeforeReminders(): int
    {
        return $this->sendReminders(
            AppointmentReminderKind::TwoHoursBefore,
            $this->candidates->eachTwoHoursBefore(...),
        );
    }

    private function sendReminders(AppointmentReminderKind $kind, callable $eachCandidate): int
    {
        $sentCount = 0;

        $eachCandidate(function (
            Patient $patient,
            Appointment $appointment,
            array $appointmentPayload,
        ) use (&$sentCount, $kind): void {
            foreach ($this->recipientsResolver->forAppointment($patient, (int) $appointment->id) as $recipient) {
                $ttl = $kind === AppointmentReminderKind::TwoHoursBefore
                    ? $this->dispatcher->ttlUntilEndOfDay()
                    : $this->dispatcher->defaultTtl();

                if ($this->dispatcher->trySend(
                    $recipient,
                    $this->reminderCache->cacheKey(
                        $kind,
                        (int) $recipient->user->id,
                        (int) $appointmentPayload['appointment_id'],
                    ),
                    $ttl,
                    new AppointmentReminderNotification($kind, $appointmentPayload, $recipient),
                )) {
                    $sentCount++;
                }
            }
        });

        return $sentCount;
    }
}
