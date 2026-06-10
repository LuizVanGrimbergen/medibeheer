<?php

use App\Enums\AppointmentStatus;
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

test('family appointments opens the paginated page for a deep linked planned appointment', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);
    $family = $familyUser->family;
    expect($family)->not->toBeNull();

    foreach (range(1, 11) as $offset) {
        Appointment::factory()->for($patient)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'starts_at' => now()->addDays($offset),
        ]);
    }

    $target = Appointment::factory()->for($patient)->create([
        'family_id' => $family->id,
        'needs_transport' => true,
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => now()->addDays(20),
    ]);

    $this->actingAs($familyUser)
        ->get(route('family.appointments', [
            'view' => 'planned',
            'appointment' => $target->id,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Appointments/Index')
            ->missing('highlighted_appointment_id'));
    $this->actingAs($familyUser)
        ->get(route('family.appointments', [
            'view' => 'planned',
            'appointment' => $target->id,
        ]))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->where('appointments.meta.current_page', 2)
            ->where('appointments.data.1.id', $target->id)));
});

test('family appointments deep links to a planned appointment with pending transport invitation', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);
    $family = $familyUser->family;
    expect($family)->not->toBeNull();

    $target = Appointment::factory()->for($patient)->create([
        'family_id' => null,
        'needs_transport' => true,
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => now()->addDays(5),
    ]);

    AppointmentTransportInvitation::query()->create([
        'appointment_id' => $target->id,
        'family_id' => $family->id,
        'invited_at' => now(),
    ]);

    $this->actingAs($familyUser)
        ->get(route('family.appointments', [
            'view' => 'planned',
            'appointment' => $target->id,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Appointments/Index')
            ->missing('highlighted_appointment_id'));
    $this->actingAs($familyUser)
        ->get(route('family.appointments', [
            'view' => 'planned',
            'appointment' => $target->id,
        ]))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->where('appointments.data.0.id', $target->id)));
});

test('family appointments ignores invalid deep link appointment ids', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)
        ->get(route('family.appointments', [
            'view' => 'planned',
            'appointment' => 999_999,
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Appointments/Index')
            ->missing('highlighted_appointment_id'));
    $this->actingAs($familyUser)
        ->get(route('family.appointments', [
            'view' => 'planned',
            'appointment' => 999_999,
        ]))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->where('appointments.meta.current_page', 1)));
});
