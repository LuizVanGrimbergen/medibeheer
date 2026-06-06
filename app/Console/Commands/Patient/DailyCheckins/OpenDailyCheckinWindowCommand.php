<?php

declare(strict_types=1);

namespace App\Console\Commands\Patient\DailyCheckins;

use App\Support\AppClock;
use Illuminate\Console\Command;

final class OpenDailyCheckinWindowCommand extends Command
{
    protected $signature = 'patient:open-daily-checkin-window';

    protected $description = 'Mark the start of each calendar-day daily check-in period (UI only; sends no notifications).';

    public function handle(): int
    {
        $today = AppClock::today()->toDateString();

        $this->components->info(
            "Daily check-in window is open for {$today} (Europe/Brussels calendar day; no push).",
        );

        return self::SUCCESS;
    }
}
