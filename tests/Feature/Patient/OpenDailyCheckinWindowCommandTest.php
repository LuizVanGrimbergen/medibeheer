<?php

use App\Support\AppClock;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;

test('open daily check-in window command runs without sending notifications', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-06-07 00:01:00', AppClock::TIMEZONE),
    );

    $this->artisan('patient:open-daily-checkin-window')
        ->assertSuccessful()
        ->expectsOutputToContain('2026-06-07')
        ->expectsOutputToContain('Europe/Brussels');

    CarbonImmutable::setTestNow();
});

test('open daily check-in window command is scheduled at 00:01 Europe Brussels', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($scheduled) => str_contains((string) $scheduled->command, 'patient:open-daily-checkin-window'));

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('1 0 * * *');
    expect($event->timezone)->toBe(AppClock::TIMEZONE);
});
