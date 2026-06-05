<?php

declare(strict_types=1);

namespace App\Services\Medications\PushReminders\PrescriptionExpiry;

use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Models\Patient;
use App\Notifications\Medications\PushReminders\PrescriptionExpiryNotification;
use App\Services\Medications\PushReminders\RecipientsResolver;
use App\Support\Medications\PushReminders\PrescriptionExpiry\ReminderCache;
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
            MedicationPrescription $prescription,
            Medication $medication,
            array $prescriptionPayload,
        ) use (&$sentCount): void {
            foreach ($this->recipientsResolver->forPrescription($patient, $medication) as $recipient) {
                $tier = PushReminderTier::from((string) $prescriptionPayload['tier']);

                $cacheKey = $this->reminderCache->cacheKey(
                    $tier,
                    (int) $recipient->user->id,
                    (int) $prescriptionPayload['prescription_id'],
                );

                if (! Cache::add($cacheKey, true, now()->addDays(self::CACHE_TTL_DAYS))) {
                    continue;
                }

                Notification::send(
                    $recipient->user,
                    new PrescriptionExpiryNotification($prescriptionPayload, $recipient),
                );

                $sentCount++;
            }
        });

        return $sentCount;
    }
}
