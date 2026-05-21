<?php

use App\Enums\MedicationType;
use Tests\TestCase;

uses(TestCase::class);
use App\Support\Medications\PatientMedicationReminderTypeLabel;

test('medication reminder type label uses dutch copy for known types', function () {
    expect(PatientMedicationReminderTypeLabel::forType(MedicationType::PILL))->toBe('Tablet / pil')
        ->and(PatientMedicationReminderTypeLabel::forType('liquid'))->toBe('Vloeistof');
});

test('medication reminder type label falls back to raw value for unknown types', function () {
    expect(PatientMedicationReminderTypeLabel::forType('unknown'))->toBe('unknown');
});
