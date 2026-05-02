<?php

use App\Models\User;

test('verified patients can visit the patient dashboard', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($user)->get(route('patient.dashboard'));

    $response->assertOk();
});

test('verified patients can visit patient medications', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($user)->get(route('patient.medications'));

    $response->assertOk();
});

test('verified patients can visit patient appointments', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($user)->get(route('patient.appointments'));

    $response->assertOk();
});

test('verified patients can visit patient inventory', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($user)->get(route('patient.inventory'));

    $response->assertOk();
});

test('verified patients can visit patient family', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($user)->get(route('patient.family'));

    $response->assertOk();
});

test('doctors cannot visit the patient dashboard', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $response = $this->actingAs($user)->get(route('patient.dashboard'));

    $response->assertForbidden();
});

test('doctors cannot visit patient medications', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $response = $this->actingAs($user)->get(route('patient.medications'));

    $response->assertForbidden();
});

test('doctors cannot visit patient inventory', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $response = $this->actingAs($user)->get(route('patient.inventory'));

    $response->assertForbidden();
});

test('doctors cannot visit patient family', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $response = $this->actingAs($user)->get(route('patient.family'));

    $response->assertForbidden();
});

test('guests are redirected when visiting the patient dashboard', function () {
    $response = $this->get(route('patient.dashboard'));

    $response->assertRedirect(route('login'));
});

test('guests are redirected when visiting patient medications', function () {
    $response = $this->get(route('patient.medications'));

    $response->assertRedirect(route('login'));
});

test('guests are redirected when visiting patient inventory', function () {
    $response = $this->get(route('patient.inventory'));

    $response->assertRedirect(route('login'));
});

test('guests are redirected when visiting patient family', function () {
    $response = $this->get(route('patient.family'));

    $response->assertRedirect(route('login'));
});
