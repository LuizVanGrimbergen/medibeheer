<?php

use App\Enums\MedicationIntakeDayPeriod;
use App\Support\Medications\MedicationDoseTimeDayPeriodResolver;

beforeEach(function () {
    $this->resolver = new MedicationDoseTimeDayPeriodResolver;
});

test('dose times map to the expected day period', function (string $time, MedicationIntakeDayPeriod $expected) {
    expect($this->resolver->resolve($time))->toBe($expected);
})->with([
    ['04:59', MedicationIntakeDayPeriod::NIGHT],
    ['05:00', MedicationIntakeDayPeriod::MORNING],
    ['11:59', MedicationIntakeDayPeriod::MORNING],
    ['12:00', MedicationIntakeDayPeriod::AFTERNOON],
    ['16:59', MedicationIntakeDayPeriod::AFTERNOON],
    ['17:00', MedicationIntakeDayPeriod::EVENING],
    ['21:59', MedicationIntakeDayPeriod::EVENING],
    ['22:00', MedicationIntakeDayPeriod::NIGHT],
    ['23:30', MedicationIntakeDayPeriod::NIGHT],
]);

test('invalid dose times throw', function (string $time) {
    $this->resolver->resolve($time);
})->throws(InvalidArgumentException::class)->with([
    '',
    'invalid',
    '24:00',
]);
