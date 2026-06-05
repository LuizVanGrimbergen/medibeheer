<?php

declare(strict_types=1);

namespace App\Console\Commands\Medications\PushReminders;

use App\Models\MedicationPrescription;
use App\Services\Medications\PushReminders\PrescriptionExpiry\CandidatesQuery;
use App\Services\PushReminders\RecipientsResolver;
use App\Support\Medications\PushReminders\PrescriptionExpiry\ReminderCache;
use App\Support\Medications\PushReminders\PushReminderTier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class PreviewPrescriptionExpiryRemindersCommand extends Command
{
    protected $signature = 'medication:preview-prescription-expiry-reminders';

    protected $description = 'List prescriptions expiring within seven days and who would receive a push reminder.';

    public function handle(
        CandidatesQuery $candidates,
        RecipientsResolver $recipientsResolver,
        ReminderCache $reminderCache,
    ): int {
        $candidateCount = 0;
        $sendableCount = 0;

        $candidates->eachCandidate(function (
            $patient,
            MedicationPrescription $prescription,
            $medication,
            array $prescriptionPayload,
        ) use (
            $recipientsResolver,
            $reminderCache,
            &$candidateCount,
            &$sendableCount,
        ): void {
            $candidateCount++;
            $tier = PushReminderTier::from((string) $prescriptionPayload['tier']);

            $this->components->info(sprintf(
                '%s (prescription #%d, medication #%d, tier %s) — %d day(s) left%s',
                $prescriptionPayload['name'],
                $prescriptionPayload['prescription_id'],
                $prescriptionPayload['medication_id'],
                $tier->value,
                $prescriptionPayload['days_remaining'],
                $prescriptionPayload['is_last_in_batch'] ? ', last in batch' : '',
            ));

            $recipients = $recipientsResolver->forPrescription($patient, $medication);

            if ($recipients === []) {
                $this->line('  No recipients with a push subscription.');

                return;
            }

            foreach ($recipients as $recipient) {
                $cacheKey = $reminderCache->cacheKey(
                    $tier,
                    (int) $recipient->user->id,
                    (int) $prescriptionPayload['prescription_id'],
                );

                $alreadySent = Cache::has($cacheKey);

                if ($alreadySent) {
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
            $this->components->warn('No prescriptions expiring within seven days.');

            return self::SUCCESS;
        }

        $this->components->info("Candidates: {$candidateCount}, sendable reminder(s) now: {$sendableCount}");

        return self::SUCCESS;
    }
}
