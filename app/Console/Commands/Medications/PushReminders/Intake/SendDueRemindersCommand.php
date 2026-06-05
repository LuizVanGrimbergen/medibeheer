<?php

declare(strict_types=1);

namespace App\Console\Commands\Medications\PushReminders\Intake;

use App\Services\Medications\PushReminders\Intake\RemindersService;
use Illuminate\Console\Command;

final class SendDueRemindersCommand extends Command
{
    protected $signature = 'patient:send-medication-due-reminders';

    protected $description = 'Send web push reminders to patients when a medication intake is due.';

    public function handle(RemindersService $reminders): int
    {
        $dueCount = $reminders->sendDueReminders();
        $missedCount = $reminders->sendMissedReminders();

        $this->components->info("Sent {$dueCount} due and {$missedCount} missed medication reminder(s).");

        return self::SUCCESS;
    }
}
