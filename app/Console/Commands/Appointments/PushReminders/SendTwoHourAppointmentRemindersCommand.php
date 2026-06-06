<?php

declare(strict_types=1);

namespace App\Console\Commands\Appointments\PushReminders;

use App\Services\Appointments\PushReminders\RemindersService;
use Illuminate\Console\Command;

final class SendTwoHourAppointmentRemindersCommand extends Command
{
    protected $signature = 'appointment:send-two-hour-reminders';

    protected $description = 'Send web push reminders two hours before scheduled appointments.';

    public function handle(RemindersService $reminders): int
    {
        $sentCount = $reminders->sendTwoHoursBeforeReminders();

        $this->components->info("Sent {$sentCount} two-hour appointment reminder(s).");

        return self::SUCCESS;
    }
}
