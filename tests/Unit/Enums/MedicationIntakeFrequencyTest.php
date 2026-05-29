<?php

use App\Enums\MedicationIntakeFrequency;

it('parses every n days frequencies', function () {
    expect(MedicationIntakeFrequency::parseEveryNDays('every_7_days'))->toBe(7);
    expect(MedicationIntakeFrequency::parseEveryNDays('every_2_days'))->toBe(2);
});

it('returns null for named frequencies in parseEveryNDays', function () {
    expect(MedicationIntakeFrequency::parseEveryNDays(MedicationIntakeFrequency::DAILY))->toBeNull();
    expect(MedicationIntakeFrequency::parseEveryNDays(MedicationIntakeFrequency::WEEKDAYS))->toBeNull();
});

it('returns null for invalid every n days patterns', function () {
    expect(MedicationIntakeFrequency::parseEveryNDays('every_1_days'))->toBeNull();
    expect(MedicationIntakeFrequency::parseEveryNDays('daily'))->toBeNull();
    expect(MedicationIntakeFrequency::parseEveryNDays('weekdays'))->toBeNull();
    expect(MedicationIntakeFrequency::parseEveryNDays(''))->toBeNull();
});
