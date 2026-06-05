<?php

declare(strict_types=1);

namespace App\Console\Commands\Appointments\PushReminders\Concerns;

use App\Models\Appointment;
use App\Models\Patient;
use App\Services\PushReminders\RecipientsResolver;
use App\Support\AppClock;
use App\Support\Appointments\PushReminders\AppointmentReminderKind;
use App\Support\Appointments\PushReminders\ReminderCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

trait PreviewsAppointmentReminders
{
    private function previewAppointmentReminders(
        callable $eachCandidate,
        RecipientsResolver $recipientsResolver,
        ReminderCache $reminderCache,
        AppointmentReminderKind $kind,
        string $emptyMessage,
    ): int {
        /** @var Command $this */
        $candidateCount = 0;
        $sendableCount = 0;

        $eachCandidate(function (
            Patient $patient,
            Appointment $appointment,
            array $appointmentPayload,
        ) use (
            $recipientsResolver,
            $reminderCache,
            $kind,
            &$candidateCount,
            &$sendableCount,
        ): void {
            $candidateCount++;
            $startsAt = AppClock::startsAtLocal($appointment);

            $this->components->info(sprintf(
                '%s · %s (appointment #%d, patient #%d) — %s %s',
                $appointmentPayload['doctor_type_label'],
                $appointmentPayload['provider_name'],
                $appointmentPayload['appointment_id'],
                (int) $patient->id,
                $startsAt->format('Y-m-d'),
                $startsAt->format('H:i'),
            ));

            $recipients = $recipientsResolver->forAppointment(
                $patient,
                (int) $appointmentPayload['appointment_id'],
            );

            if ($recipients === []) {
                $this->line('  No recipients with a push subscription.');

                return;
            }

            foreach ($recipients as $recipient) {
                $cacheKey = $reminderCache->cacheKey(
                    $kind,
                    (int) $recipient->user->id,
                    (int) $appointmentPayload['appointment_id'],
                );

                if (Cache::has($cacheKey)) {
                    $this->line(sprintf(
                        '  - %s (%s #%d): skipped — already sent (cache)',
                        $recipient->user->name,
                        $recipient->audience->value,
                        $recipient->user->id,
                    ));

                    continue;
                }

                $sendableCount++;
                $this->line(sprintf(
                    '  - %s (%s #%d): would send → %s',
                    $recipient->user->name,
                    $recipient->audience->value,
                    $recipient->user->id,
                    $recipient->openUrl,
                ));
            }
        });

        if ($candidateCount === 0) {
            $this->components->warn($emptyMessage);

            return self::SUCCESS;
        }

        $this->components->info("Candidates: {$candidateCount}, sendable reminder(s) now: {$sendableCount}");

        return self::SUCCESS;
    }
}
