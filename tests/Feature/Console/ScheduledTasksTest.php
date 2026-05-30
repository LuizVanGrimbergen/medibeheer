<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Schedule;

test('production registers exactly two scheduled artisan commands', function () {
    $commandEvents = collect(Schedule::events())
        ->filter(fn (Event $event): bool => $event->command !== null);

    expect($commandEvents)->toHaveCount(2);
});

test('medication due reminder command is scheduled every minute without overlapping', function () {
    $event = collect(Schedule::events())->first(
        fn (Event $event): bool => str_contains((string) $event->command, 'patient:send-medication-due-reminders'),
    );

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('* * * * *');
    expect($event->withoutOverlapping)->toBeTrue();
});

test('privacy purge command is scheduled daily', function () {
    $event = collect(Schedule::events())->first(
        fn (Event $event): bool => str_contains((string) $event->command, 'privacy:purge-expired-data'),
    );

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('0 0 * * *');
    expect($event->withoutOverlapping)->toBeFalse();
});
