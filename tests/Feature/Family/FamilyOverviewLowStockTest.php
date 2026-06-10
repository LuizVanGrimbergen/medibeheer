<?php

use App\Enums\MedicationIntakeFrequency;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Models\MedicationStock;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-05-14 12:00:00');
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('family overview lists linked patients with critical medication stock', function () {
    $patientUser = User::factory()->patient()->create(['name' => 'Sophie Maas']);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
    ]);
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '5',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '2',
        'dose_quantity' => '0.5',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $response = $this->actingAs($familyUser)->get(route('family.overview'));

    $response->assertOk();
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->component('Family/Overview/Index')
        ->has('low_stock_patients', 1)
        ->where('low_stock_patients.0.patient_name', 'Sophie Maas')
        ->has('low_stock_patients.0.medications', 1)
        ->where('low_stock_patients.0.medications.0.name', 'Paracetamol')
        ->where('low_stock_patients.0.medications.0.supply_estimate_days', 5)
        ->where(
            'low_stock_patients.0.medications_url',
            route('family.medications', ['medication' => $medication->id], absolute: false),
        )));
});

test('family overview omits patients when medication stock is not critical', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '30',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '2',
        'dose_quantity' => '0.5',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Family/Overview/Index')
            ->has('low_stock_patients', 0)));
});

test('family overview omits low stock patients without a patient link', function () {
    $familyUser = User::factory()->familyMember()->create();

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Family/Overview/Index')
            ->has('low_stock_patients', 0)));
});
