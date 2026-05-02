<?php

use App\Models\User;

test('patients receive the patient dashboard inertia page from the patient route', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($user)->get(route('patient.dashboard'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Dashboard');
});

test('doctors receive the doctor dashboard inertia page from the doctor dashboard route', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $response = $this->actingAs($user)->get(route('doctor.dashboard'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Doctor/Dashboard');
});

test('family members receive the family overview inertia page from the family overview route', function () {
    $user = User::factory()->familyMember()->create();

    $response = $this->actingAs($user)->get(route('family.overview'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Family/Overview');
});
