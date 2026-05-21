<?php

declare(strict_types=1);

namespace App\Console\Commands\Patient;

use App\Services\Medications\PatientMedicationDueRemindersService;
use Illuminate\Console\Command;

final class SendPatientMedicationDueReminderNotificationsCommand extends Command
{
    protected $signature = 'patient:send-medication-due-reminders';

    protected $description = 'Send web push reminders to patients when a medication intake is due.';

    public function handle(PatientMedicationDueRemindersService $reminders): int
    {
        $dueCount = $reminders->sendDueReminders();
        $missedCount = $reminders->sendMissedReminders();

        $this->components->info("Sent {$dueCount} due and {$missedCount} missed medication reminder(s).");

        return self::SUCCESS;
    }
}
