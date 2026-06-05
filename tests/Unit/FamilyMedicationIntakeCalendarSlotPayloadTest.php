<?php

use App\Support\Family\FamilyMedicationIntakeCalendarSlotPayload;

test('family medication intake calendar slot payload keeps only family view fields', function () {
    $fullSlot = [
        'medication_id' => 4,
        'medication_schedule_id' => 4,
        'dose_time' => '22:00',
        'snooze_minutes' => 30,
        'intake_window_state' => 'before',
        'day_period' => 'night',
        'meal_timing' => 'after_food',
        'intake_frequency' => 'daily',
        'intake_weekdays' => null,
        'name' => 'Atorvastatine',
        'type_medication' => 'pill',
        'dose' => '40',
        'dose_unit' => 'piece',
        'note' => 's Avonds innemen.',
        'taken_at' => null,
        'stocks' => [['current_stock' => '440']],
        'supply_estimate_days' => 11,
        'supply_estimate_quality' => 'approx',
        'intake_date' => '2026-06-30',
    ];

    expect(FamilyMedicationIntakeCalendarSlotPayload::fromFullSlot($fullSlot))
        ->toBe([
            'medication_schedule_id' => 4,
            'dose_time' => '22:00',
            'snooze_minutes' => 30,
            'day_period' => 'night',
            'name' => 'Atorvastatine',
            'type_medication' => 'pill',
            'taken_at' => null,
            'intake_date' => '2026-06-30',
        ]);
});

test('family medication intake calendar slot payload maps taken_at when present', function () {
    $fullSlot = [
        'medication_schedule_id' => 1,
        'dose_time' => '09:00',
        'snooze_minutes' => 30,
        'day_period' => 'morning',
        'name' => 'Paracetamol',
        'type_medication' => 'pill',
        'taken_at' => '2026-05-15T09:05:00+00:00',
        'intake_date' => '2026-05-15',
    ];

    expect(FamilyMedicationIntakeCalendarSlotPayload::fromFullSlot($fullSlot)['taken_at'])
        ->toBe('2026-05-15T09:05:00+00:00');
});
