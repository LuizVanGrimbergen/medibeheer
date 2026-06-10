<?php

use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-05-14 12:00:00');
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('family overview lists linked patients with expiring prescriptions', function () {
    $patientUser = User::factory()->patient()->create(['name' => 'Sophie Maas']);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Magnesiumcitraat',
    ]);

    MedicationPrescription::factory()
        ->forMedication($medication)
        ->create([
            'prescription_expiry_date' => '2026-05-19',
        ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $response = $this->actingAs($familyUser)->get(route('family.overview'));

    $response->assertOk();
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->component('Family/Overview/Index')
        ->has('expiring_prescription_patients', 1)
        ->where('expiring_prescription_patients.0.patient_name', 'Sophie Maas')
        ->has('expiring_prescription_patients.0.prescriptions', 1)
        ->where(
            'expiring_prescription_patients.0.prescriptions.0.medication_name',
            'Magnesiumcitraat',
        )
        ->where('expiring_prescription_patients.0.prescriptions.0.days_remaining', 5)
        ->where('expiring_prescription_patients.0.prescriptions.0.is_last_in_batch', false)
        ->where(
            'expiring_prescription_patients.0.medications_url',
            route('family.medications', ['medication' => $medication->id], absolute: false),
        )));
});

test('family overview exposes whether a prescription is the last in batch', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();

    MedicationPrescription::factory()
        ->forMedication($medication)
        ->lastInBatch()
        ->create([
            'prescription_expiry_date' => '2026-05-20',
        ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Family/Overview/Index')
            ->where('expiring_prescription_patients.0.prescriptions.0.is_last_in_batch', true)));
});

test('family overview omits patients when prescription expiry is only a warning', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();

    MedicationPrescription::factory()
        ->forMedication($medication)
        ->create([
            'prescription_expiry_date' => '2026-05-28',
        ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Family/Overview/Index')
            ->has('expiring_prescription_patients', 0)));
});

test('family overview omits patients when prescription expiry is not urgent', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();

    MedicationPrescription::factory()
        ->forMedication($medication)
        ->create([
            'prescription_expiry_date' => '2026-12-31',
        ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Family/Overview/Index')
            ->has('expiring_prescription_patients', 0)));
});

test('family overview omits expiring prescription patients without a patient link', function () {
    $familyUser = User::factory()->familyMember()->create();

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Family/Overview/Index')
            ->has('expiring_prescription_patients', 0)));
});
