<?php

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-05-14 12:00:00');
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('family overview lists upcoming appointments with accepted transport for the family', function () {
    $patientUser = User::factory()->patient()->create(['name' => 'Sophie Maas']);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);
    $family = $familyUser->family;
    expect($family)->not->toBeNull();

    $appointment = Appointment::factory()->for($patient)->create([
        'family_id' => $family->id,
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

    $response = $this->actingAs($familyUser)->get(route('family.overview'));

    $response->assertOk();
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->component('Family/Overview/Index')
        ->has('accepted_transport_appointments', 1)
        ->where('accepted_transport_appointments.0.patient_name', 'Sophie Maas')
        ->where('accepted_transport_appointments.0.provider_name', 'Huisartsenpost Meedhuizen')
        ->where('accepted_transport_appointments.0.street', 'van der Venhof')
        ->where('accepted_transport_appointments.0.house_number', '26')
        ->where('accepted_transport_appointments.0.postal_code', '6671DV')
        ->where('accepted_transport_appointments.0.city', 'Meedhuizen')
        ->where(
            'accepted_transport_appointments.0.appointments_url',
            route('family.appointments', ['view' => 'planned', 'appointment' => $appointment->id], absolute: false),
        )));
});

test('family overview omits transport accepted by another family', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);
    $otherFamily = User::factory()->familyMember()->create()->family;
    expect($otherFamily)->not->toBeNull();

    Appointment::factory()->for($patient)->create([
        'family_id' => $otherFamily->id,
        'needs_transport' => true,
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => now()->addDay(),
    ]);

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Family/Overview/Index')
            ->has('accepted_transport_appointments', 0)));
});

test('family overview omits past appointments with accepted transport', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);
    $family = $familyUser->family;
    expect($family)->not->toBeNull();

    Appointment::factory()->for($patient)->create([
        'family_id' => $family->id,
        'needs_transport' => true,
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => now()->subDay(),
    ]);

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->has('accepted_transport_appointments', 0)));
});
