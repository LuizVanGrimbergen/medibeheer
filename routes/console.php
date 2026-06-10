<?php

use App\Support\AppClock;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| Local: `php artisan schedule:work` (included in `composer run dev`).
| Production (Combell): cron every minute → `scripts/run-scheduler.sh`.
| Queue worker cron: `scripts/run-queue-worker.sh` (see deploy/combell-production.md).
|
*/

Schedule::command('privacy:purge-expired-data')->daily();

Schedule::command('patient:open-daily-checkin-window')
    ->dailyAt('00:01')
    ->timezone(AppClock::TIMEZONE)
    ->withoutOverlapping();

Schedule::command('patient:send-medication-due-reminders')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('medication:send-low-stock-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();

Schedule::command('medication:send-prescription-expiry-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();

Schedule::command('appointment:send-two-day-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();

Schedule::command('appointment:send-two-hour-reminders')
    ->everyMinute()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Manual commands (app/Console/Commands)
|--------------------------------------------------------------------------
|
| patient:open-daily-checkin-window — daily check-in UI window (00:01 Brussels; also scheduled)
|
*/
