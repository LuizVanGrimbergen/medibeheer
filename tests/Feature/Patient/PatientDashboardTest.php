<?php

use App\Enums\DailyMoodScore;
use App\Models\DailyCheckin;
use App\Models\User;
use App\Support\Medications\MedicationIntakeClock;

test('patient dashboard includes today check-in when one exists for today', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => MedicationIntakeClock::today()->toDateString(),
        'mood_score' => DailyMoodScore::GOOD,
        'note' => null,
    ]);

    $this->actingAs($user)
        ->get(route('patient.dashboard'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->where('today_checkin.mood_score', 'good')
            ->etc()));
});

test('patient dashboard has no today check-in payload before first check-in', function () {
    $user = User::factory()->patient()->create();

    $this->actingAs($user)
        ->get(route('patient.dashboard'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page->where('today_checkin', null)));
});

test('verified patients can visit the patient dashboard', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($user)->get(route('patient.dashboard'));

    $response->assertOk();
});

test('patient dashboard reports whether the patient has medications', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $this->actingAs($user)
        ->get(route('patient.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('has_medications', false));
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

test('verified patients can visit patient prescriptions', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->actingAs($user)->get(route('patient.prescriptions'));

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

test('doctors cannot visit patient prescriptions', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $response = $this->actingAs($user)->get(route('patient.prescriptions'));

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

test('guests are redirected when visiting patient prescriptions', function () {
    $response = $this->get(route('patient.prescriptions'));

    $response->assertRedirect(route('login'));
});

test('guests are redirected when visiting patient family', function () {
    $response = $this->get(route('patient.family'));

    $response->assertRedirect(route('login'));
});
