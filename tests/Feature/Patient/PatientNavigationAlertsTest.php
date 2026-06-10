<?php

use App\Enums\MedicationIntakeFrequency;
use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Models\MedicationSchedule;
use App\Models\MedicationStock;
use App\Models\User;
use App\Support\Medications\MedicationIntakeClock;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

test('patient navigation shares critical inventory alert when supply is low', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-15 10:00:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();

    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '5',
    ]);

    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '2',
        'dose_quantity' => '0.5',
        'dose_time' => '08:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    $this->actingAs($user)
        ->get(route('patient.dashboard'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn (Assert $page) => $page
            ->where('patient_navigation.inventory', 'critical')
            ->where('patient_navigation.prescriptions', null)));

    CarbonImmutable::setTestNow();
});

test('patient navigation shares warning prescription alert when expiry is soon', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-15 10:00:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();

    MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-05-25',
        'completed_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('patient.dashboard'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn (Assert $page) => $page
            ->where('patient_navigation.inventory', null)
            ->where('patient_navigation.prescriptions', 'warning')));

    CarbonImmutable::setTestNow();
});

test('patient navigation has no alerts when supply and prescriptions are healthy', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-15 10:00:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();

    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '200',
    ]);

    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '2',
        'dose_quantity' => '0.5',
        'dose_time' => '08:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-12-31',
        'completed_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('patient.dashboard'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn (Assert $page) => $page
            ->where('patient_navigation.inventory', null)
            ->where('patient_navigation.prescriptions', null)));

    CarbonImmutable::setTestNow();
});
