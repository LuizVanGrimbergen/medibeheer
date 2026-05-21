<?php

use App\Enums\MedicationIntakeFrequency;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Models\User;
use App\Support\Medications\PatientRecentPushMedicationMarkStore;
use Inertia\Testing\AssertableInertia as Assert;

test('patient push success page shows medication name from server cache', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    app(PatientRecentPushMedicationMarkStore::class)->remember($patient->id, 'Paracetamol');

    $this->actingAs($user)
        ->get(route('patient.medication-push-mark.success'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Patient/MedicationPushMarkSuccess')
            ->where('medication_name', 'Paracetamol'));
});

test('patient push success page accepts medication query when store is empty', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
    ]);

    $this->actingAs($user)
        ->get(route('patient.medication-push-mark.success', ['medication' => 'Paracetamol']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Patient/MedicationPushMarkSuccess')
            ->where('medication_name', 'Paracetamol'));
});

test('patient dashboard shows pending push medication mark from server cache', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
    ]);

    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '09:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    app(PatientRecentPushMedicationMarkStore::class)->remember($patient->id, 'Paracetamol');

    $this->actingAs($user)
        ->get(route('patient.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Patient/Dashboard')
            ->where('pending_push_medication_mark', 'Paracetamol'));
});

test('patient can acknowledge pending push medication mark', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    app(PatientRecentPushMedicationMarkStore::class)->remember($patient->id, 'Paracetamol');

    $this->actingAs($user)
        ->post(route('patient.medication-push-mark.ack'))
        ->assertNoContent();

    expect(app(PatientRecentPushMedicationMarkStore::class)->peek($patient->id))->toBeNull();
});
