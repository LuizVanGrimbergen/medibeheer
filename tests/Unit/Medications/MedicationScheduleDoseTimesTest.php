<?php

use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationScheduleDoseTimes;
use Carbon\CarbonImmutable;

test('parses legacy comma separated dose times with default snooze', function () {
    $slots = MedicationScheduleDoseTimes::parse('08:00, 20:00');

    expect($slots)->toHaveCount(2)
        ->and($slots[0]['time'])->toBe('08:00')
        ->and($slots[0]['snooze_minutes'])->toBe(30)
        ->and($slots[1]['time'])->toBe('20:00');
});

test('parses encoded dose times with snooze minutes', function () {
    $encoded = MedicationScheduleDoseTimes::mergeDisplayTimesAndSnoozes('09:00, 12:00', '60, 30');

    expect($encoded)->toBe('09:00|60, 12:00|30')
        ->and(MedicationScheduleDoseTimes::displayTimes($encoded))->toBe('09:00, 12:00')
        ->and(MedicationScheduleDoseTimes::displaySnoozeMinutes($encoded))->toBe('60, 30')
        ->and(MedicationScheduleDoseTimes::sortedTimes($encoded))->toBe(['09:00', '12:00']);
});

test('intake window ends after snooze minutes from dose time', function () {
    $raw = '09:00|60';
    $tz = MedicationIntakeClock::TIMEZONE;
    $before = CarbonImmutable::parse('2026-05-18 08:59:00', $tz);
    $start = CarbonImmutable::parse('2026-05-18 09:00:00', $tz);
    $inside = CarbonImmutable::parse('2026-05-18 09:45:00', $tz);
    $end = CarbonImmutable::parse('2026-05-18 10:00:00', $tz);
    $after = CarbonImmutable::parse('2026-05-18 10:01:00', $tz);

    expect(MedicationScheduleDoseTimes::isWithinIntakeWindow('09:00', $raw, $before))->toBeFalse()
        ->and(MedicationScheduleDoseTimes::isWithinIntakeWindow('09:00', $raw, $start))->toBeTrue()
        ->and(MedicationScheduleDoseTimes::isWithinIntakeWindow('09:00', $raw, $inside))->toBeTrue()
        ->and(MedicationScheduleDoseTimes::isWithinIntakeWindow('09:00', $raw, $end))->toBeTrue()
        ->and(MedicationScheduleDoseTimes::isWithinIntakeWindow('09:00', $raw, $after))->toBeFalse();
});

test('intake window state uses amsterdam wall clock for dose times', function () {
    $raw = '16:04|30';
    $now = CarbonImmutable::parse('2026-05-18 17:00:00', MedicationIntakeClock::TIMEZONE);

    expect(MedicationScheduleDoseTimes::resolveIntakeWindowState('16:04', $raw, $now))->toBe('past')
        ->and(MedicationScheduleDoseTimes::resolveIntakeWindowState('16:04', $raw, $now->setTime(16, 10)))->toBe('within');
});
