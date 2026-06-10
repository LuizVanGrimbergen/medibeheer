<?php

use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationListStatus;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\MedicationStock;
use App\Models\User;
use App\Support\Medications\MedicationIntakeClock;
use Carbon\CarbonImmutable;

test('linked family members see patient medications on family medications', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $response = $this->actingAs($familyUser)->get(route('family.medications'));

    $response->assertOk();
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->component('Family/Medications/Index')
        ->has('medications.data', 1)
        ->where('medications.data.0.name', 'Paracetamol')));
});

test('linked family members see ended and removed medications with list status on family medications', function () {
    CarbonImmutable::setTestNow('2026-05-19 10:00:00');

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $activeMedication = Medication::factory()->for($patient)->create(['name' => 'Actief']);
    MedicationSchedule::factory()->forMedication($activeMedication)->create([
        'end_date' => '2026-12-31',
    ]);

    $endedMedication = Medication::factory()->for($patient)->create(['name' => 'Verlopen']);
    MedicationSchedule::factory()->forMedication($endedMedication)->create([
        'end_date' => MedicationIntakeClock::today()->subDay()->toDateString(),
    ]);

    $removedMedication = Medication::factory()->for($patient)->create(['name' => 'Verwijderd']);
    MedicationSchedule::factory()->forMedication($removedMedication)->create();
    $removedMedication->delete();

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $response = $this->actingAs($familyUser)->get(route('family.medications'));

    $response->assertOk();
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->has('medications.data', 3)
        ->where('medications.data.0.list_status', MedicationListStatus::REMOVED->value)
        ->where('medications.data.0.name', 'Verwijderd')
        ->where('medications.data.1.list_status', MedicationListStatus::ENDED->value)
        ->where('medications.data.1.name', 'Verlopen')
        ->where('medications.data.2.list_status', MedicationListStatus::ACTIVE->value)
        ->where('medications.data.2.name', 'Actief')));

    CarbonImmutable::setTestNow();
});

test('linked family members see medication intake calendar data on family medications', function () {
    CarbonImmutable::setTestNow('2026-05-15 10:00:00');

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
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
        'taken_at' => now(),
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $response = $this->actingAs($familyUser)->get(route('family.medications', [
        'calendar_month' => '2026-05',
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Family/Medications/Index')
        ->where('medication_calendar_month', '2026-05'));
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->where('medication_calendar_days.14.date', '2026-05-15')
        ->where('medication_calendar_days.14.status', 'complete')
        ->has('medication_calendar_slots', 31)
        ->where('medication_calendar_slots.14.name', 'Paracetamol')
        ->where('medication_calendar_slots.14.taken_at', fn ($value) => $value !== null)
        ->where('medication_calendar_slots.14', function (mixed $slot): bool {
            $values = is_array($slot) ? $slot : $slot->all();

            return ! array_key_exists('note', $values)
                && ! array_key_exists('stocks', $values)
                && ! array_key_exists('supply_estimate_days', $values);
        })));

    CarbonImmutable::setTestNow();
});

test('linked family members can update medication stock from family medications', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->has(MedicationStock::factory(), 'stocks')
        ->create();

    $stock = $medication->stocks()->first();
    expect($stock)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $response = $this->from(route('family.medications'))
        ->actingAs($familyUser)
        ->put(route('family.medications.stocks.update', [$medication, $stock]), [
            'current_stock' => '42',
        ]);

    $response->assertRedirect(route('family.medications'));

    $stock->refresh();
    expect($stock->current_stock)->toBe('42');
});

test('family members without a patient link see empty medications on family medications', function () {
    $familyUser = User::factory()->familyMember()->create();

    $response = $this->actingAs($familyUser)->get(route('family.medications'));

    $response->assertOk();
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->component('Family/Medications/Index')
        ->has('medications.data', 0)
        ->has('medication_calendar_days', 0)
        ->has('medication_calendar_slots', 0)));
});
