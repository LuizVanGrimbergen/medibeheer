<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;

test('verified doctors can visit the doctor dashboard', function () {
    $user = User::factory()->doctor()->create();

    $response = $this->actingAs($user)->get(route('doctor.dashboard'));

    $response->assertOk();
});

test('verified doctors can visit the doctor patients index', function () {
    $user = User::factory()->doctor()->create();

    $response = $this->actingAs($user)->get(route('doctor.patients'));

    $response->assertOk();
});

test('patients cannot visit the doctor dashboard', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->get(route('doctor.dashboard'));

    $response->assertForbidden();
});

test('patients cannot visit the doctor patients index', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->get(route('doctor.patients'));

    $response->assertForbidden();
});

test('family members cannot visit the doctor dashboard', function () {
    $user = User::factory()->familyMember()->create();

    $response = $this->actingAs($user)->get(route('doctor.dashboard'));

    $response->assertForbidden();
});

test('guests are redirected when visiting the doctor dashboard', function () {
    $response = $this->get(route('doctor.dashboard'));

    $response->assertRedirect(route('login'));
});

test('guests are redirected when visiting the doctor patients index', function () {
    $response = $this->get(route('doctor.patients'));

    $response->assertRedirect(route('login'));
});

test('doctors only see patients linked on the doctor patient pivot', function () {
    $doctorUser = User::factory()->doctor()->create();
    $doctor = Doctor::factory()->for($doctorUser)->create();
    $linkedPatient = Patient::factory()->create();
    $otherPatient = Patient::factory()->create();
    $doctor->patients()->attach($linkedPatient);

    $response = $this->actingAs($doctorUser)->get(route('doctor.patients'));

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain($linkedPatient->user->public_id);
    expect($content)->not->toContain($otherPatient->user->public_id);
});
