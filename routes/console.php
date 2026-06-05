<?php

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

Schedule::command('patient:send-medication-due-reminders')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('medication:send-low-stock-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();

Schedule::command('medication:send-prescription-expiry-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Manual / diagnostic commands (app/Console/Commands)
|--------------------------------------------------------------------------
|
| patient:send-test-push-notification       — immediate test push
| patient:preview-medication-due-reminders  — slots due this minute
| patient:diagnose-medication-push-reminders — VAPID, subscription, schedule debug
| medication:preview-low-stock-reminders         — list critical supply + recipients
| medication:send-low-stock-reminders              — seven-day supply web push (patient + family)
| medication:preview-prescription-expiry-reminders — list expiring prescriptions + recipients
| medication:send-prescription-expiry-reminders    — seven-day prescription expiry push
|
*/
