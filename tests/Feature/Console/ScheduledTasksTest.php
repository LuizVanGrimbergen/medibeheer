<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Schedule;

test('medication due reminder command is scheduled every minute', function () {
    $event = collect(Schedule::events())->first(
        fn (Event $event): bool => str_contains((string) $event->command, 'patient:send-medication-due-reminders'),
    );

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('* * * * *');
});

test('privacy purge command is scheduled daily', function () {
    $event = collect(Schedule::events())->first(
        fn (Event $event): bool => str_contains((string) $event->command, 'privacy:purge-expired-data'),
    );

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('0 0 * * *');
});
