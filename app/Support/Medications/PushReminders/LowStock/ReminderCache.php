<?php

declare(strict_types=1);

namespace App\Support\Medications\PushReminders\LowStock;

use App\Models\Medication;
use App\Models\MedicationStock;
use App\Services\Medications\MedicationSupplyEstimateService;
use App\Services\PushReminders\RecipientsResolver;
use App\Support\Medications\MedicationUrgencyToneResolver;
use App\Support\Medications\PushReminders\PushReminderTier;
use Illuminate\Support\Facades\Cache;

final class ReminderCache
{
    private const string KEY_PREFIX = 'medication-low-stock-reminder';

    public function __construct(
        private readonly MedicationSupplyEstimateService $supplyEstimateService,
        private readonly RecipientsResolver $recipientsResolver,
    ) {}

    public function cacheKey(PushReminderTier $tier, int $recipientUserId, int $medicationId): string
    {
        return self::KEY_PREFIX.":{$tier->value}:{$recipientUserId}:{$medicationId}";
    }

    public function forgetAllTiersForMedicationRecipients(Medication $medication): void
    {
        $patient = $medication->patient;

        if ($patient === null) {
            return;
        }

        foreach ($this->recipientsResolver->linkedUsersFor($patient) as $user) {
            foreach (PushReminderTier::cases() as $tier) {
                Cache::forget($this->cacheKey($tier, (int) $user->id, (int) $medication->id));
            }
        }
    }

    public function forgetUrgentTierForMedicationRecipients(Medication $medication): void
    {
        $patient = $medication->patient;

        if ($patient === null) {
            return;
        }

        foreach ($this->recipientsResolver->linkedUsersFor($patient) as $user) {
            Cache::forget($this->cacheKey(
                PushReminderTier::Urgent,
                (int) $user->id,
                (int) $medication->id,
            ));
        }
    }

    public function clearIfSupplyRecovered(MedicationStock $stock): void
    {
        if (! $stock->wasChanged('current_stock')) {
            return;
        }

        $medication = $stock->medication;

        if ($medication === null) {
            return;
        }

        $medication->loadMissing(['schedules.weekdays', 'stocks', 'patient']);

        $estimate = $this->supplyEstimateService->estimate($medication);

        if ($estimate['quality'] !== 'approx' || $estimate['days'] === null) {
            return;
        }

        if ($estimate['days'] > MedicationUrgencyToneResolver::CRITICAL_MAX_DAYS) {
            $this->forgetAllTiersForMedicationRecipients($medication);

            return;
        }

        if ($estimate['days'] > MedicationUrgencyToneResolver::REMINDER_URGENT_MAX_DAYS) {
            $this->forgetUrgentTierForMedicationRecipients($medication);
        }
    }
}
