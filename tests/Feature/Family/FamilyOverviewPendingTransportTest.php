<?php

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Models\AppointmentTransportInvitation;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-05-14 12:00:00');
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('family overview lists upcoming appointments with pending transport invitations', function () {
    $patientUser = User::factory()->patient()->create(['name' => 'Sophie Maas']);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);
    $family = $familyUser->family;
    expect($family)->not->toBeNull();

    $appointment = Appointment::factory()->for($patient)->create([
        'family_id' => null,
        'needs_transport' => true,
        'status' => AppointmentStatus::SCHEDULED,
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER,
        'provider_name' => 'Huisartsenpost Meedhuizen',
        'street' => 'van der Venhof',
        'house_number' => '26',
        'postal_code' => '6671DV',
        'city' => 'Meedhuizen',
        'starts_at' => now()->addDays(3),
    ]);

    $invitation = AppointmentTransportInvitation::query()->create([
        'appointment_id' => $appointment->id,
        'family_id' => $family->id,
        'invited_at' => now(),
    ]);

    $response = $this->actingAs($familyUser)->get(route('family.overview'));

    $response->assertOk();
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->component('Family/Overview/Index')
        ->has('pending_transport_appointments', 1)
        ->where('pending_transport_appointments.0.invitation_id', $invitation->id)
        ->where('pending_transport_appointments.0.patient_name', 'Sophie Maas')
        ->where('pending_transport_appointments.0.provider_name', 'Huisartsenpost Meedhuizen')
        ->where('pending_transport_appointments.0.street', 'van der Venhof')
        ->where('pending_transport_appointments.0.postal_code', '6671DV')
        ->where('pending_transport_appointments.0.city', 'Meedhuizen')
        ->where(
            'pending_transport_appointments.0.appointments_url',
            route('family.appointments', ['view' => 'planned', 'appointment' => $appointment->id], absolute: false),
        )
        ->where(
            'pending_transport_appointments.0.accept_url',
            route('family.transport-invitations.accept', $invitation, absolute: false),
        )
        ->where(
            'pending_transport_appointments.0.decline_url',
            route('family.transport-invitations.decline', $invitation, absolute: false),
        )));
});

test('family overview omits pending transport when another family already accepted', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);
    $family = $familyUser->family;
    expect($family)->not->toBeNull();

    $otherFamily = User::factory()->familyMember()->create()->family;
    expect($otherFamily)->not->toBeNull();

    $appointment = Appointment::factory()->for($patient)->create([
        'family_id' => $otherFamily->id,
        'needs_transport' => true,
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => now()->addDay(),
    ]);

    AppointmentTransportInvitation::query()->create([
        'appointment_id' => $appointment->id,
        'family_id' => $family->id,
        'invited_at' => now(),
    ]);

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Family/Overview/Index')
            ->has('pending_transport_appointments', 0)));
});

test('family overview omits declined pending transport invitations', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);
    $family = $familyUser->family;
    expect($family)->not->toBeNull();

    $appointment = Appointment::factory()->for($patient)->create([
        'family_id' => null,
        'needs_transport' => true,
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => now()->addDay(),
    ]);

    AppointmentTransportInvitation::query()->create([
        'appointment_id' => $appointment->id,
        'family_id' => $family->id,
        'invited_at' => now(),
        'declined_at' => now(),
    ]);

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->has('pending_transport_appointments', 0)));
});

test('family overview omits past appointments with pending transport invitations', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);
    $family = $familyUser->family;
    expect($family)->not->toBeNull();

    $appointment = Appointment::factory()->for($patient)->create([
        'family_id' => null,
        'needs_transport' => true,
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => now()->subDay(),
    ]);

    AppointmentTransportInvitation::query()->create([
        'appointment_id' => $appointment->id,
        'family_id' => $family->id,
        'invited_at' => now(),
    ]);

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->has('pending_transport_appointments', 0)));
});
