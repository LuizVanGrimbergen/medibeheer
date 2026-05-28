<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| Local: `php artisan schedule:work` (included in `composer run dev`).
| Production: cron `* * * * * php /path/to/artisan schedule:run`.
|
*/

Schedule::command('privacy:purge-expired-data')->daily();

Schedule::command('patient:send-medication-due-reminders')
    ->everyMinute()
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Manual / diagnostic commands (app/Console/Commands)
|--------------------------------------------------------------------------
|
| patient:send-test-push-notification       — immediate test push
| patient:preview-medication-due-reminders  — slots due this minute
| patient:diagnose-medication-push-reminders — VAPID, subscription, schedule debug
|
*/
