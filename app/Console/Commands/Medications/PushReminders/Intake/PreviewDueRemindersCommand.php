<?php

declare(strict_types=1);

namespace App\Console\Commands\Medications\PushReminders\Intake;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Medications\PushReminders\Intake\RemindersService;
use Illuminate\Console\Command;

final class PreviewDueRemindersCommand extends Command
{
    protected $signature = 'patient:preview-medication-due-reminders {user? : Patient user id (default: all patients with push)}';

    protected $description = 'List medication intake slots that would receive a push reminder right now (exact dose_time minute, from medication_schedules).';

    public function handle(RemindersService $reminders): int
    {
        $userId = $this->argument('user');

        $users = $userId !== null
            ? User::query()->whereKey((int) $userId)->where('role', UserRole::PATIENT)->get()
            : User::query()
                ->where('role', UserRole::PATIENT)
                ->whereHas('pushSubscriptions', function ($query): void {
                    $query->where('endpoint', 'not like', '%push.example.test%');
                })
                ->get();

        if ($users->isEmpty()) {
            $this->components->warn('No matching patient(s) found.');

            return self::FAILURE;
        }

        $totalDue = 0;

        foreach ($users as $user) {
            $patient = $user->patient;

            if ($patient === null) {
                continue;
            }

            $dueSlots = $reminders->dueReminderSlotsForPatient($patient);

            $this->components->info("{$user->name} (user #{$user->id}, patient #{$patient->id})");

            if ($dueSlots === []) {
                $this->line('  No intake at the exact dose_time minute right now.');

                continue;
            }

            foreach ($dueSlots as $slot) {
                $totalDue++;
                $this->line(sprintf(
                    '  - %s at %s (schedule #%d, window: %s)',
                    (string) ($slot['name'] ?? ''),
                    (string) ($slot['dose_time'] ?? ''),
                    (int) ($slot['medication_schedule_id'] ?? 0),
                    (string) ($slot['intake_window_state'] ?? ''),
                ));
            }
        }

        $this->components->info("Total due slot(s) now: {$totalDue}");

        return self::SUCCESS;
    }
}
