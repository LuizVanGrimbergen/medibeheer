<?php

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
    $response->assertInertia(fn ($page) => $page
        ->component('Doctor/Patients/Index')
        ->has('patients')
        ->has('incoming_invitations')
    );
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

test('family members cannot visit the patient family page with doctor data', function () {
    $user = User::factory()->familyMember()->create();

    $this->actingAs($user)
        ->get(route('patient.family'))
        ->assertForbidden();
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
    $doctor = $doctorUser->doctor;
    $linkedPatient = Patient::factory()->create();
    $otherPatient = Patient::factory()->create();
    $doctor->patients()->attach($linkedPatient);

    $response = $this->actingAs($doctorUser)->get(route('doctor.dashboard', [
        'patient' => $linkedPatient->user->public_id,
    ]));

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain($linkedPatient->user->public_id);
    expect($content)->not->toContain($otherPatient->user->public_id);
});

test('doctors can view a linked patient overview with calendar data on the dashboard', function () {
    $doctorUser = User::factory()->doctor()->create();
    $doctor = $doctorUser->doctor;
    $linkedPatient = Patient::factory()->create();
    $doctor->patients()->attach($linkedPatient);

    $response = $this->actingAs($doctorUser)->get(route('doctor.dashboard', [
        'patient' => $linkedPatient->user->public_id,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Doctor/Dashboard')
        ->has('patient_overview', fn ($overview) => $overview
            ->where('selected_patient.public_id', $linkedPatient->user->public_id)
            ->where('selected_patient.name', $linkedPatient->user->name)
            ->has('medication_calendar_month')
            ->has('medication_calendar_days')
            ->has('medication_calendar_slots')
            ->has('wellbeing_calendar_month')
            ->has('wellbeing_calendar_checkins')
        )
    );
});

test('doctors cannot view an unlinked patient overview on the dashboard', function () {
    $doctorUser = User::factory()->doctor()->create();
    $otherPatient = Patient::factory()->create();

    $response = $this->actingAs($doctorUser)->get(route('doctor.dashboard', [
        'patient' => $otherPatient->user->public_id,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Doctor/Dashboard')
        ->where('patient_overview', null)
    );
});

test('doctor patients route redirects patient overview queries to the dashboard', function () {
    $doctorUser = User::factory()->doctor()->create();
    $doctor = $doctorUser->doctor;
    $linkedPatient = Patient::factory()->create();
    $doctor->patients()->attach($linkedPatient);

    $response = $this->actingAs($doctorUser)->get(route('doctor.patients', [
        'patient' => $linkedPatient->user->public_id,
        'calendar_month' => '2026-05',
    ]));

    $response->assertRedirect(route('doctor.dashboard', [
        'patient' => $linkedPatient->user->public_id,
        'calendar_month' => '2026-05',
    ]));
});

test('doctors can unlink a linked patient', function () {
    $doctorUser = User::factory()->doctor()->create();
    $doctor = $doctorUser->doctor;
    $linkedPatient = Patient::factory()->create();
    $doctor->patients()->attach($linkedPatient);

    $response = $this->actingAs($doctorUser)->delete(
        route('doctor.patients.links.destroy', ['linkedPatient' => $linkedPatient->user->public_id]),
    );

    $response->assertRedirect(route('doctor.patients'));

    expect($doctor->fresh()->patients()->whereKey($linkedPatient->id)->exists())->toBeFalse();
});

test('doctors cannot unlink a patient that is not linked to them', function () {
    $doctorUser = User::factory()->doctor()->create();
    $otherPatient = Patient::factory()->create();

    $this->actingAs($doctorUser)
        ->delete(route('doctor.patients.links.destroy', ['linkedPatient' => $otherPatient->user->public_id]))
        ->assertNotFound();
});
