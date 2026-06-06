<?php

declare(strict_types=1);

namespace App\Services\Medications\PushReminders\LowStock;

use App\Models\Medication;
use App\Models\Patient;
use App\Notifications\Medications\PushReminders\LowStockNotification;
use App\Services\PushReminders\PushReminderDispatcher;
use App\Services\PushReminders\RecipientsResolver;
use App\Support\Medications\PushReminders\LowStock\ReminderCache;
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
            Medication $medication,
            array $medicationPayload,
        ) use (&$sentCount): void {
            foreach ($this->recipientsResolver->forMedication($patient, $medication) as $recipient) {
                $tier = PushReminderTier::from((string) $medicationPayload['tier']);

                if ($this->dispatcher->trySend(
                    $recipient,
                    $this->reminderCache->cacheKey(
                        $tier,
                        (int) $recipient->user->id,
                        (int) $medicationPayload['medication_id'],
                    ),
                    $this->dispatcher->defaultTtl(),
                    new LowStockNotification($medicationPayload, $recipient),
                )) {
                    $sentCount++;
                }
            }
        });

        return $sentCount;
    }
}
