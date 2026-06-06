<?php

declare(strict_types=1);

namespace App\Console\Commands\Medications\PushReminders;

use App\Models\Medication;
use App\Services\Medications\PushReminders\LowStock\CandidatesQuery;
use App\Services\PushReminders\RecipientsResolver;
use App\Support\Medications\PushReminders\LowStock\ReminderCache;
use App\Support\Medications\PushReminders\PushReminderTier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class PreviewLowStockRemindersCommand extends Command
{
    protected $signature = 'medication:preview-low-stock-reminders';

    protected $description = 'List medications at or below seven days supply and who would receive a push reminder.';

    public function handle(
        CandidatesQuery $candidates,
        RecipientsResolver $recipientsResolver,
        ReminderCache $reminderCache,
    ): int {
        $candidateCount = 0;
        $sendableCount = 0;

        $candidates->eachCandidate(function (
            $patient,
            Medication $medication,
            array $medicationPayload,
        ) use (
            $recipientsResolver,
            $reminderCache,
            &$candidateCount,
            &$sendableCount,
        ): void {
            $candidateCount++;
            $tier = PushReminderTier::from((string) $medicationPayload['tier']);

            $this->components->info(sprintf(
                '%s (medication #%d, patient #%d, tier %s) — %d day(s) left',
                $medicationPayload['name'],
                $medicationPayload['medication_id'],
                (int) $patient->id,
                $tier->value,
                $medicationPayload['supply_estimate_days'],
            ));

            $recipients = $recipientsResolver->forMedication($patient, $medication);

            if ($recipients === []) {
                $this->line('  No recipients with a push subscription.');

                return;
            }

            foreach ($recipients as $recipient) {
                $cacheKey = $reminderCache->cacheKey(
                    $tier,
                    (int) $recipient->user->id,
                    (int) $medicationPayload['medication_id'],
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
            $this->components->warn('No medications at or below seven days with a known supply estimate.');

            return self::SUCCESS;
        }

        $this->components->info("Candidates: {$candidateCount}, sendable reminder(s) now: {$sendableCount}");

        return self::SUCCESS;
    }
}
