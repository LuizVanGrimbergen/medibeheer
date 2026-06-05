<?php

use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\PushReminders\Intake\ReminderTiming;
use Carbon\CarbonImmutable;

test('reminder matches only the calendar minute of the dose time', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:00:45', MedicationIntakeClock::TIMEZONE),
    );

    expect(ReminderTiming::isExactDoseTimeMinute('09:00'))->toBeTrue()
        ->and(ReminderTiming::isExactDoseTimeMinute('09:05'))->toBeFalse();

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:01:00', MedicationIntakeClock::TIMEZONE),
    );

    expect(ReminderTiming::isExactDoseTimeMinute('09:00'))->toBeFalse();

    CarbonImmutable::setTestNow();
});

test('missed reminder matches only the calendar minute when the snooze window ends', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:30:00', MedicationIntakeClock::TIMEZONE),
    );

    expect(ReminderTiming::isExactSnoozeEndMinute('09:00', 30))->toBeTrue()
        ->and(ReminderTiming::isExactSnoozeEndMinute('09:00', 0))->toBeFalse()
        ->and(ReminderTiming::isExactSnoozeEndMinute('09:00', 30, CarbonImmutable::parse('2026-05-19 09:29:00', MedicationIntakeClock::TIMEZONE)))->toBeFalse();

    CarbonImmutable::setTestNow();
});
