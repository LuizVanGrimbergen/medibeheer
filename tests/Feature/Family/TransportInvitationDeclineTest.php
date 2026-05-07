<?php

use App\Models\Appointment;
use App\Models\AppointmentTransportInvitation;
use App\Models\User;

test('linked family member can decline a transport invitation', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->family;
    expect($family)->not->toBeNull();
    $family->patients()->syncWithoutDetaching([$patient->id]);

    $appointment = Appointment::factory()->for($patient)->create([
        'needs_transport' => true,
    ]);

    $invitation = AppointmentTransportInvitation::query()->create([
        'appointment_id' => $appointment->id,
        'family_id' => $family->id,
        'invited_at' => now(),
    ]);

    $this->actingAs($familyUser)
        ->post(route('family.transport-invitations.decline', $invitation))
        ->assertRedirect(route('family.appointments'));

    expect($appointment->fresh()->family_id)->toBeNull();
    expect($invitation->fresh()->declined_at)->not->toBeNull();
});

test('family member cannot decline another familys transport invitation', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUserA = User::factory()->familyMember()->create();
    $familyA = $familyUserA->family;
    expect($familyA)->not->toBeNull();
    $familyA->patients()->syncWithoutDetaching([$patient->id]);

    $familyUserB = User::factory()->familyMember()->create();

    $appointment = Appointment::factory()->for($patient)->create([
        'needs_transport' => true,
    ]);

    $invitation = AppointmentTransportInvitation::query()->create([
        'appointment_id' => $appointment->id,
        'family_id' => $familyA->id,
        'invited_at' => now(),
    ]);

    $this->actingAs($familyUserB)
        ->post(route('family.transport-invitations.decline', $invitation))
        ->assertNotFound();

    expect($invitation->fresh()->declined_at)->toBeNull();
});
