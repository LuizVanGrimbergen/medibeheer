<?php

use App\Models\Medication;
use App\Models\MedicationStock;
use App\Models\User;

test('patient inventory inertia page includes paginated medications with stocks', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    Medication::factory()
        ->for($patient)
        ->has(MedicationStock::factory(), 'stocks')
        ->create();

    $response = $this->actingAs($user)->get(route('patient.inventory'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Inventory');
    $response->assertInertia(fn ($page) => $page
        ->component('Patient/Inventory')
        ->has('medications.data', 1)
        ->has('medications.data.0.stocks', 1)
        ->where('medications.data.0.supply_estimate_quality', 'unknown')
        ->has('medications.meta'));
});

test('patients updating medication stock from inventory return to inventory', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->has(MedicationStock::factory(), 'stocks')
        ->create();

    $stock = $medication->stocks()->first();
    expect($stock)->not->toBeNull();

    $response = $this->from(route('patient.inventory'))
        ->actingAs($user)
        ->put(route('patient.medications.stocks.update', [$medication, $stock]), [
            'current_stock' => '100',
            'low_stock' => '8',
        ]);

    $response->assertRedirect(route('patient.inventory'));

    $stock->refresh();
    expect($stock->current_stock)->toBe('100');
    expect($stock->low_stock)->toBe('8');
});
