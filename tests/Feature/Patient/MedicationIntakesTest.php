<?php

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationType;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\User;
use Carbon\CarbonImmutable;

test('patient dashboard includes today medication intake slots', function () {
    CarbonImmutable::setTestNow('2026-05-15 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE,
        'type_medication' => MedicationType::PILL,
        'note' => 'Met water innemen.',
    ]);

    MedicationSchedule::factory()->forMedication($medication)->create([
        'meal_timing' => MedicationMealTiming::UNRELATED,
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '2',
        'dose_quantity' => '1',
        'dose_time' => '08:00, 20:00',
        'start_date' => '2026-05-01',
        'end_date' => '2026-12-31',
    ]);

    $response = $this->actingAs($user)->get(route('patient.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Patient/Dashboard')
        ->has('today_medication_intakes', 2)
        ->where('today_medication_intakes.0.name', 'Paracetamol')
        ->where('today_medication_intakes.0.dose_time', '08:00')
        ->where('today_medication_intakes.0.day_period', 'morning')
        ->where('today_medication_intakes.0.note', 'Met water innemen.')
        ->where('today_medication_intakes.0.taken_at', null)
        ->where('today_medication_intakes.1.dose_time', '20:00')
        ->where('today_medication_intakes.1.day_period', 'evening'));

    CarbonImmutable::setTestNow();
});

test('patients can mark a due medication intake as taken', function () {
    CarbonImmutable::setTestNow('2026-05-15 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '09:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    $response = $this->actingAs($user)->post(route('patient.medication-intakes.store'), [
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '09:00',
    ]);

    $response->assertRedirect(route('patient.dashboard'));

    expect(MedicationIntake::query()->where('patient_id', $patient->id)
        ->where('medication_schedule_id', $schedule->id)
        ->whereDate('intake_date', '2026-05-15')
        ->where('dose_time', '09:00')
        ->exists())->toBeTrue();

    CarbonImmutable::setTestNow();
});

test('marking the same intake again updates the existing record', function () {
    CarbonImmutable::setTestNow('2026-05-15 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '09:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    MedicationIntake::factory()->forSchedule($schedule)->create([
        'intake_date' => '2026-05-15',
        'dose_time' => '09:00',
        'taken_at' => now()->subHour(),
    ]);

    $this->actingAs($user)->post(route('patient.medication-intakes.store'), [
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '09:00',
    ])->assertRedirect(route('patient.dashboard'));

    expect(MedicationIntake::query()->count())->toBe(1);

    CarbonImmutable::setTestNow();
});

test('weekday-only medications are omitted on non-scheduled days', function () {
    CarbonImmutable::setTestNow('2026-05-17 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::WEEKDAYS,
        'dose_time' => '08:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);
    $schedule->syncIntakeWeekdays([1, 2, 3, 4, 5]);

    $this->actingAs($user)
        ->get(route('patient.dashboard'))
        ->assertInertia(fn ($page) => $page->has('today_medication_intakes', 0));

    CarbonImmutable::setTestNow();
});
