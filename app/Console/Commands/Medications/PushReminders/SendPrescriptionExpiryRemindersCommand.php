<?php

declare(strict_types=1);

namespace App\Console\Commands\Medications\PushReminders;

use App\Services\Medications\PushReminders\PrescriptionExpiry\RemindersService;
use Illuminate\Console\Command;

final class SendPrescriptionExpiryRemindersCommand extends Command
{
    protected $signature = 'medication:send-prescription-expiry-reminders';

    protected $description = 'Send web push reminders when a medication prescription expires within seven days.';

    public function handle(RemindersService $reminders): int
    {
        $sentCount = $reminders->sendReminders();

        $this->components->info("Sent {$sentCount} prescription expiry reminder(s).");

        return self::SUCCESS;
    }
}
