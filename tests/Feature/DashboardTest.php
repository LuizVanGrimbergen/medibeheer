<?php

use App\Models\User;

test('patients visiting the dashboard route are redirected to the patient dashboard', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('patient.dashboard'));
});

test('patients receive the patient dashboard inertia page from the patient route', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($user)->get(route('patient.dashboard'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Dashboard');
});

test('doctors receive the default dashboard inertia page from the dashboard route', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Dashboard');
});

test('family members receive the default dashboard inertia page from the dashboard route', function () {
    $user = User::factory()->create(['role' => 'family_member']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Dashboard');
});
