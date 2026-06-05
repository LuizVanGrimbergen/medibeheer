<?php

declare(strict_types=1);

namespace App\Console\Commands\Appointments\PushReminders;

use App\Services\Appointments\PushReminders\RemindersService;
use Illuminate\Console\Command;

final class SendTwoDayAppointmentRemindersCommand extends Command
{
    protected $signature = 'appointment:send-two-day-reminders';

    protected $description = 'Send web push reminders two calendar days before scheduled appointments.';

    public function handle(RemindersService $reminders): int
    {
        $sentCount = $reminders->sendTwoDaysBeforeReminders();

        $this->components->info("Sent {$sentCount} two-day appointment reminder(s).");

        return self::SUCCESS;
    }
}
