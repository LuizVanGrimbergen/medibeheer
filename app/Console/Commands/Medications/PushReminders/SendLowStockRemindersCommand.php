<?php

declare(strict_types=1);

namespace App\Console\Commands\Medications\PushReminders;

use App\Services\Medications\PushReminders\LowStock\RemindersService;
use Illuminate\Console\Command;

final class SendLowStockRemindersCommand extends Command
{
    protected $signature = 'medication:send-low-stock-reminders';

    protected $description = 'Send web push reminders when a medication supply estimate is at or below seven days.';

    public function handle(RemindersService $reminders): int
    {
        $sentCount = $reminders->sendReminders();

        $this->components->info("Sent {$sentCount} low stock medication reminder(s).");

        return self::SUCCESS;
    }
}
