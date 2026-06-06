<?php

use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Models\User;

test('patient prescriptions inertia page includes paginated prescriptions with medication', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->create();

    MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-12-31',
    ]);

    $response = $this->actingAs($user)->get(route('patient.prescriptions'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Prescriptions');
    $response->assertInertia(fn ($page) => $page
        ->component('Patient/Prescriptions')
        ->has('prescriptions.data', 1)
        ->where('prescriptions.data.0.medication.id', $medication->id)
        ->where('prescriptions.data.0.prescription_expiry_date', '2026-12-31')
        ->where('prescriptions.data.0.pickup_status', 'pending')
        ->has('prescriptions.meta')
        ->has('medication_choices', 1)
        ->where('medication_choices.0.id', $medication->id));
});

test('multiple prescriptions for the same medication appear as separate items', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->create();

    MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-06-02',
    ]);
    MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-06-16',
    ]);
    MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-06-30',
    ]);

    $response = $this->actingAs($user)->get(route('patient.prescriptions'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('prescriptions.data', 3)
        ->where('prescriptions.data.0.prescription_expiry_date', '2026-06-02')
        ->where('prescriptions.data.1.prescription_expiry_date', '2026-06-16')
        ->where('prescriptions.data.2.prescription_expiry_date', '2026-06-30')
        ->where('prescriptions.data.0.medication.id', $medication->id)
        ->where('prescriptions.data.1.medication.id', $medication->id)
        ->where('prescriptions.data.2.medication.id', $medication->id));
});

test('patients can store multiple prescriptions for a medication', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->create();

    $response = $this->actingAs($user)->post(
        route('patient.medications.prescriptions.store', $medication),
        [
            'quantity' => 2,
            'prescription_expiry_dates' => ['2026-06-01', '2026-12-01'],
        ],
    );

    $response->assertRedirect(route('patient.prescriptions'));

    expect($medication->prescriptions()->count())->toBe(2);
    expect(
        $medication->prescriptions()
            ->orderBy('prescription_expiry_date')
            ->pluck('prescription_expiry_date')
            ->map(fn ($date) => $date?->format('Y-m-d'))
            ->all(),
    )->toBe(['2026-06-01', '2026-12-01']);
    expect(
        $medication->prescriptions()
            ->orderBy('prescription_expiry_date')
            ->pluck('is_last_in_batch')
            ->all(),
    )->toBe([false, true]);
});

test('storing prescriptions requires matching quantity and expiry dates', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->create();

    $response = $this->actingAs($user)->from(route('patient.prescriptions'))
        ->post(route('patient.medications.prescriptions.store', $medication), [
            'quantity' => 2,
            'prescription_expiry_dates' => ['2026-06-01'],
        ]);

    $response->assertRedirect(route('patient.prescriptions'));
    $response->assertSessionHasErrors('prescription_expiry_dates');

    expect($medication->prescriptions()->count())->toBe(0);
});

test('patients can mark a prescription as completed and it disappears from the list', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->create();

    $prescription = MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-06-02',
    ]);
    MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-06-30',
    ]);

    $response = $this->actingAs($user)->patch(
        route('patient.prescriptions.update', $prescription),
        ['completed' => true],
    );

    $response->assertRedirect(route('patient.prescriptions'));

    expect($prescription->fresh()->completed_at)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('patient.prescriptions'))
        ->assertInertia(fn ($page) => $page
            ->has('prescriptions.data', 1)
            ->where('prescriptions.data.0.prescription_expiry_date', '2026-06-30'));
});

test('completed prescriptions are excluded from the prescriptions list', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->create();

    MedicationPrescription::factory()->forMedication($medication)->completed()->create([
        'prescription_expiry_date' => '2026-06-02',
    ]);

    $this->actingAs($user)
        ->get(route('patient.prescriptions'))
        ->assertInertia(fn ($page) => $page
            ->where('prescriptions.meta.total', 0));
});

test('patients can update prescription pickup status', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->create();

    $prescription = MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-06-02',
    ]);

    $this->actingAs($user)->patch(
        route('patient.prescriptions.update', $prescription),
        ['pickup_status' => 'picked_up'],
    )->assertRedirect(route('patient.prescriptions'));

    expect($prescription->fresh()->pickup_status->value)->toBe('picked_up');
    expect($prescription->fresh()->completed_at)->not->toBeNull();

    $this->actingAs($user)
        ->get(route('patient.prescriptions'))
        ->assertInertia(fn ($page) => $page
            ->where('prescriptions.meta.total', 0));

    $this->actingAs($user)->patch(
        route('patient.prescriptions.update', $prescription),
        ['pickup_status' => 'pending'],
    )->assertRedirect(route('patient.prescriptions'));

    expect($prescription->fresh()->pickup_status->value)->toBe('pending');
    expect($prescription->fresh()->completed_at)->toBeNull();

    $this->actingAs($user)
        ->get(route('patient.prescriptions'))
        ->assertInertia(fn ($page) => $page
            ->has('prescriptions.data', 1)
            ->where('prescriptions.data.0.pickup_status', 'pending'));
});

test('patients can update a prescription expiry date', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->create();

    $prescription = MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-06-02',
    ]);

    $response = $this->actingAs($user)->patch(
        route('patient.prescriptions.update', $prescription),
        ['prescription_expiry_date' => '2026-12-15'],
    );

    $response->assertRedirect(route('patient.prescriptions'));

    expect($prescription->fresh()->prescription_expiry_date?->format('Y-m-d'))
        ->toBe('2026-12-15');
});

test('patients can delete a prescription', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->create();

    $prescription = MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-06-02',
    ]);

    $response = $this->actingAs($user)->delete(
        route('patient.prescriptions.destroy', $prescription),
    );

    $response->assertRedirect(route('patient.prescriptions'));

    expect($prescription->fresh()->trashed())->toBeTrue();

    $this->actingAs($user)
        ->get(route('patient.prescriptions'))
        ->assertInertia(fn ($page) => $page
            ->where('prescriptions.meta.total', 0));
});

test('patients cannot delete another patients prescription', function () {
    $firstUser = User::factory()->patient()->create();
    $secondUser = User::factory()->patient()->create();

    $medication = Medication::factory()
        ->for($firstUser->patient)
        ->create();

    $prescription = MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-06-02',
    ]);

    $response = $this->actingAs($secondUser)->delete(
        route('patient.prescriptions.destroy', $prescription),
    );

    $response->assertForbidden();

    expect($prescription->fresh()->trashed())->toBeFalse();
});

test('guests are redirected when visiting patient prescriptions', function () {
    $response = $this->get(route('patient.prescriptions'));

    $response->assertRedirect(route('login'));
});

test('doctors cannot visit patient prescriptions', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $response = $this->actingAs($user)->get(route('patient.prescriptions'));

    $response->assertForbidden();
});
