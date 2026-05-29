<?php

use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationScheduleDoseTimes;
use Carbon\CarbonImmutable;

test('parses comma separated dose times with separate snooze column', function () {
    $slots = MedicationScheduleDoseTimes::parse('08:00, 20:00', '45, 60');

    expect($slots)->toHaveCount(2)
        ->and($slots[0]['time'])->toBe('08:00')
        ->and($slots[0]['snooze_minutes'])->toBe(45)
        ->and($slots[1]['time'])->toBe('20:00')
        ->and($slots[1]['snooze_minutes'])->toBe(60);
});

test('defaults snooze minutes per slot when snooze column is missing', function () {
    $slots = MedicationScheduleDoseTimes::parse('08:00, 20:00');

    expect($slots[0]['snooze_minutes'])->toBe(30)
        ->and($slots[1]['snooze_minutes'])->toBe(30);
});

test('intake window ends after snooze minutes from dose time', function () {
    $tz = MedicationIntakeClock::TIMEZONE;
    $before = CarbonImmutable::parse('2026-05-18 08:59:00', $tz);
    $start = CarbonImmutable::parse('2026-05-18 09:00:00', $tz);
    $inside = CarbonImmutable::parse('2026-05-18 09:45:00', $tz);
    $end = CarbonImmutable::parse('2026-05-18 10:00:00', $tz);
    $after = CarbonImmutable::parse('2026-05-18 10:01:00', $tz);

    expect(MedicationScheduleDoseTimes::isWithinIntakeWindow('09:00', '09:00', '60', $before))->toBeFalse()
        ->and(MedicationScheduleDoseTimes::isWithinIntakeWindow('09:00', '09:00', '60', $start))->toBeTrue()
        ->and(MedicationScheduleDoseTimes::isWithinIntakeWindow('09:00', '09:00', '60', $inside))->toBeTrue()
        ->and(MedicationScheduleDoseTimes::isWithinIntakeWindow('09:00', '09:00', '60', $end))->toBeTrue()
        ->and(MedicationScheduleDoseTimes::isWithinIntakeWindow('09:00', '09:00', '60', $after))->toBeFalse();
});

test('intake window state uses amsterdam wall clock for dose times', function () {
    $now = CarbonImmutable::parse('2026-05-18 17:00:00', MedicationIntakeClock::TIMEZONE);

    expect(MedicationScheduleDoseTimes::resolveIntakeWindowState('16:04', '16:04', '30', $now))->toBe('past')
        ->and(MedicationScheduleDoseTimes::resolveIntakeWindowState('16:04', '16:04', '30', $now->setTime(16, 10)))->toBe('within');
});
