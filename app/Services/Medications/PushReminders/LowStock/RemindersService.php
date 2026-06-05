<?php

declare(strict_types=1);

namespace App\Services\Medications\PushReminders\LowStock;

use App\Models\Medication;
use App\Models\Patient;
use App\Notifications\Medications\PushReminders\LowStockNotification;
use App\Services\Medications\PushReminders\RecipientsResolver;
use App\Support\Medications\PushReminders\LowStock\ReminderCache;
use App\Support\Medications\PushReminders\PushReminderTier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

final class RemindersService
{
    private const int CACHE_TTL_DAYS = 90;

    public function __construct(
        private readonly CandidatesQuery $candidates,
        private readonly RecipientsResolver $recipientsResolver,
        private readonly ReminderCache $reminderCache,
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

                $cacheKey = $this->reminderCache->cacheKey(
                    $tier,
                    (int) $recipient->user->id,
                    (int) $medicationPayload['medication_id'],
                );

                if (! Cache::add($cacheKey, true, now()->addDays(self::CACHE_TTL_DAYS))) {
                    continue;
                }

                Notification::send(
                    $recipient->user,
                    new LowStockNotification($medicationPayload, $recipient),
                );

                $sentCount++;
            }
        });

        return $sentCount;
    }
}
