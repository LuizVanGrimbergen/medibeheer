<?php

declare(strict_types=1);

namespace App\Services\Medications\PushReminders\PrescriptionExpiry;

use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Models\Patient;
use App\Notifications\Medications\PushReminders\PrescriptionExpiryNotification;
use App\Services\PushReminders\PushReminderDispatcher;
use App\Services\PushReminders\RecipientsResolver;
use App\Support\Medications\PushReminders\PrescriptionExpiry\ReminderCache;
use App\Support\Medications\PushReminders\PushReminderTier;

final class RemindersService
{
    public function __construct(
        private readonly CandidatesQuery $candidates,
        private readonly RecipientsResolver $recipientsResolver,
        private readonly ReminderCache $reminderCache,
        private readonly PushReminderDispatcher $dispatcher,
    ) {}

    public function sendReminders(): int
    {
        $sentCount = 0;

        $this->candidates->eachCandidate(function (
            Patient $patient,
            MedicationPrescription $prescription,
            Medication $medication,
            array $prescriptionPayload,
        ) use (&$sentCount): void {
            foreach ($this->recipientsResolver->forPrescription($patient, $medication) as $recipient) {
                $tier = PushReminderTier::from((string) $prescriptionPayload['tier']);

                if ($this->dispatcher->trySend(
                    $recipient,
                    $this->reminderCache->cacheKey(
                        $tier,
                        (int) $recipient->user->id,
                        (int) $prescriptionPayload['prescription_id'],
                    ),
                    $this->dispatcher->defaultTtl(),
                    new PrescriptionExpiryNotification($prescriptionPayload, $recipient),
                )) {
                    $sentCount++;
                }
            }
        });

        return $sentCount;
    }
}
