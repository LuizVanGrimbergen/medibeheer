<?php

declare(strict_types=1);

namespace App\Support\Medications\PushReminders\PrescriptionExpiry;

use App\Models\MedicationPrescription;
use App\Services\PushReminders\RecipientsResolver;
use App\Support\Medications\MedicationUrgencyToneResolver;
use App\Support\Medications\PushReminders\PushReminderTier;
use Illuminate\Support\Facades\Cache;

final class ReminderCache
{
    private const string KEY_PREFIX = 'prescription-expiry-reminder';

    public function __construct(
        private readonly MedicationUrgencyToneResolver $urgencyToneResolver,
        private readonly RecipientsResolver $recipientsResolver,
    ) {}

    public function cacheKey(PushReminderTier $tier, int $recipientUserId, int $prescriptionId): string
    {
        return self::KEY_PREFIX.":{$tier->value}:{$recipientUserId}:{$prescriptionId}";
    }

    public function forgetAllTiersForPrescriptionRecipients(MedicationPrescription $prescription): void
    {
        $patient = $prescription->patient;

        if ($patient === null) {
            return;
        }

        foreach ($this->recipientsResolver->linkedUsersFor($patient) as $user) {
            foreach (PushReminderTier::cases() as $tier) {
                Cache::forget($this->cacheKey($tier, (int) $user->id, (int) $prescription->id));
            }
        }
    }

    public function forgetUrgentTierForPrescriptionRecipients(MedicationPrescription $prescription): void
    {
        $patient = $prescription->patient;

        if ($patient === null) {
            return;
        }

        foreach ($this->recipientsResolver->linkedUsersFor($patient) as $user) {
            Cache::forget($this->cacheKey(
                PushReminderTier::Urgent,
                (int) $user->id,
                (int) $prescription->id,
            ));
        }
    }

    public function clearIfNoLongerCritical(MedicationPrescription $prescription): void
    {
        if ($prescription->completed_at !== null) {
            $this->forgetAllTiersForPrescriptionRecipients($prescription);

            return;
        }

        $daysRemaining = $this->urgencyToneResolver->prescriptionExpiryDaysRemainingFor(
            $prescription,
        );

        if ($daysRemaining === null || $daysRemaining > MedicationUrgencyToneResolver::CRITICAL_MAX_DAYS) {
            $this->forgetAllTiersForPrescriptionRecipients($prescription);

            return;
        }

        if ($daysRemaining > MedicationUrgencyToneResolver::REMINDER_URGENT_MAX_DAYS) {
            $this->forgetUrgentTierForPrescriptionRecipients($prescription);
        }
    }
}
