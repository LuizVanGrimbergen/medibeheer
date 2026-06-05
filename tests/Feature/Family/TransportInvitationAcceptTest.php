<?php

use App\Models\Appointment;
use App\Models\AppointmentTransportInvitation;
use App\Models\User;

test('linked family member can accept a transport invitation', function () {
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
        ->from(route('family.overview'))
        ->post(route('family.transport-invitations.accept', $invitation))
        ->assertRedirect(route('family.overview'))
        ->assertSessionHas('success', __('transport_invitation.flash.accepted'));

    expect($appointment->fresh()->family_id)->toBe($family->id);
    expect($invitation->fresh()->accepted_at)->not->toBeNull();
});

test('accepting transport from appointments redirects back with success', function () {
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
        ->from(route('family.appointments'))
        ->post(route('family.transport-invitations.accept', $invitation))
        ->assertRedirect(route('family.appointments'))
        ->assertSessionHas('success', __('transport_invitation.flash.accepted'));
});

test('family member cannot accept another familys transport invitation', function () {
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
        ->post(route('family.transport-invitations.accept', $invitation))
        ->assertNotFound();

    expect($appointment->fresh()->family_id)->toBeNull();
});
