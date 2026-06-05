<?php

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Models\User;

test('patients see appointments on the appointments page', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    Appointment::factory()->for($patient)->count(2)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $response = $this->actingAs($user)->get(route('patient.appointments'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Appointments');
    $response->assertInertia(fn ($page) => $page
        ->has('appointments.data', 2)
        ->where('appointments.meta.total', 2)
        ->where('appointment_view', 'planned')
        ->where('appointment_tab_totals.planned', 2)
        ->where('appointment_tab_totals.completed', 0));
});

test('patient appointments index paginates scheduled appointments', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    Appointment::factory()->for($patient)->count(12)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)->get(route('patient.appointments'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('appointments.data', 10)
            ->where('appointments.meta.current_page', 1)
            ->where('appointments.meta.last_page', 2)
            ->where('appointments.meta.total', 12)
            ->where('appointment_view', 'planned')
            ->where('appointment_tab_totals.planned', 12)
            ->where('appointment_tab_totals.completed', 0));

    $this->actingAs($user)->get(route('patient.appointments', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('appointments.data', 2)
            ->where('appointments.meta.current_page', 2)
            ->where('appointment_view', 'planned'));
});

test('patient appointments opens the paginated page for a deep linked planned appointment', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    foreach (range(1, 11) as $offset) {
        Appointment::factory()->for($patient)->create([
            'status' => AppointmentStatus::SCHEDULED,
            'starts_at' => now()->addDays($offset),
        ]);
    }

    $target = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => now()->addDays(20),
    ]);

    $this->actingAs($user)
        ->get(route('patient.appointments', ['appointment' => $target->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Appointments')
            ->where('appointments.meta.current_page', 2)
            ->where('appointments.data.1.id', $target->id));
});

test('patient appointments ignores invalid deep link appointment ids', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    Appointment::factory()->for($patient)->count(2)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)
        ->get(route('patient.appointments', ['appointment' => 999_999]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Appointments')
            ->where('appointments.meta.current_page', 1));
});

test('patient appointments ignores deep link for completed appointments', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $completed = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::DONE,
        'starts_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get(route('patient.appointments', ['appointment' => $completed->id]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Appointments')
            ->where('appointments.meta.current_page', 1)
            ->where('appointments.data', []));
});

test('patients can create an appointment', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $startsAt = now()->addWeek();

    $response = $this->actingAs($user)->post(route('patient.appointments.store'), [
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
        'provider_name' => 'City Clinic',
        'street' => 'Main Street',
        'house_number' => '1',
        'postal_code' => '1234 AB',
        'city' => 'Amsterdam',
        'starts_at' => $startsAt->toDateTimeString(),
        'notes' => 'Bring ID.',
        'status' => AppointmentStatus::SCHEDULED->value,
    ]);

    $response->assertRedirect(route('patient.appointments'));

    $this->assertDatabaseHas('appointments', [
        'patient_id' => $patient->id,
        'status' => AppointmentStatus::SCHEDULED->value,
    ]);

    $appointment = Appointment::query()->where('patient_id', $patient->id)->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->doctor_type)->toBe(DoctorType::GENERAL_PRACTITIONER);
    expect($appointment->provider_name)->toBe('City Clinic');
    expect($appointment->street)->toBe('Main Street');
    expect($appointment->house_number)->toBe('1');
    expect($appointment->postal_code)->toBe('1234 AB');
    expect($appointment->city)->toBe('Amsterdam');
    expect($appointment->notes)->toBe('Bring ID.');
});

test('patients can create an appointment without a house number', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $startsAt = now()->addWeek();

    $this->actingAs($user)->post(route('patient.appointments.store'), [
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
        'provider_name' => 'City Clinic',
        'street' => 'Main Street',
        'postal_code' => '1234 AB',
        'city' => 'Amsterdam',
        'starts_at' => $startsAt->toDateTimeString(),
        'notes' => null,
        'status' => AppointmentStatus::SCHEDULED->value,
    ])->assertRedirect(route('patient.appointments'));

    $appointment = Appointment::query()->where('patient_id', $patient->id)->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->house_number)->toBe('');
});

test('patients can create an appointment without an address when transport is not needed', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $startsAt = now()->addWeek();

    $this->actingAs($user)->post(route('patient.appointments.store'), [
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
        'provider_name' => '',
        'street' => '',
        'house_number' => '',
        'postal_code' => '',
        'city' => '',
        'starts_at' => $startsAt->toDateTimeString(),
        'needs_transport' => false,
        'notes' => null,
        'status' => AppointmentStatus::SCHEDULED->value,
    ])->assertRedirect(route('patient.appointments'));

    $appointment = Appointment::query()->where('patient_id', $patient->id)->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->street)->toBe('');
    expect($appointment->postal_code)->toBe('');
    expect($appointment->city)->toBe('');
});

test('patients cannot create a transport appointment without an address', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->family;
    expect($family)->not->toBeNull();
    $family->patients()->syncWithoutDetaching([$patient->id]);

    $startsAt = now()->addWeek();

    $this->actingAs($patientUser)->post(route('patient.appointments.store'), [
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
        'provider_name' => 'City Clinic',
        'street' => '',
        'house_number' => '',
        'postal_code' => '',
        'city' => '',
        'starts_at' => $startsAt->toDateTimeString(),
        'needs_transport' => true,
        'transport_family_ids' => [(int) $family->id],
        'notes' => null,
        'status' => AppointmentStatus::SCHEDULED->value,
    ])->assertSessionHasErrors(['street', 'postal_code', 'city']);
});

test('patients can create an appointment without a provider name', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $startsAt = now()->addWeek();

    $this->actingAs($user)->post(route('patient.appointments.store'), [
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
        'provider_name' => '',
        'street' => 'Main Street',
        'house_number' => '1',
        'postal_code' => '1234 AB',
        'city' => 'Amsterdam',
        'starts_at' => $startsAt->toDateTimeString(),
        'notes' => null,
        'status' => AppointmentStatus::SCHEDULED->value,
    ])->assertRedirect(route('patient.appointments'));

    $appointment = Appointment::query()->where('patient_id', $patient->id)->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->provider_name)->toBe('');
});

test('patients can request transport when creating an appointment', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->family;
    expect($family)->not->toBeNull();
    $family->patients()->syncWithoutDetaching([$patient->id]);

    $secondFamilyUser = User::factory()->familyMember()->create();
    $secondFamily = $secondFamilyUser->family;
    expect($secondFamily)->not->toBeNull();
    $secondFamily->patients()->syncWithoutDetaching([$patient->id]);

    $startsAt = now()->addWeek();

    $this->actingAs($patientUser)->post(route('patient.appointments.store'), [
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
        'provider_name' => 'City Clinic',
        'street' => 'Main Street',
        'house_number' => '1',
        'postal_code' => '1234 AB',
        'city' => 'Amsterdam',
        'starts_at' => $startsAt->toDateTimeString(),
        'needs_transport' => true,
        'transport_family_ids' => [(int) $family->id],
        'notes' => null,
        'status' => AppointmentStatus::SCHEDULED->value,
    ])->assertRedirect(route('patient.appointments'));

    $appointment = Appointment::query()->where('patient_id', $patient->id)->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->needs_transport)->toBeTrue();

    $this->assertDatabaseHas('appointment_transport_invitations', [
        'appointment_id' => $appointment->id,
        'family_id' => $family->id,
    ]);

    $this->assertDatabaseMissing('appointment_transport_invitations', [
        'appointment_id' => $appointment->id,
        'family_id' => $secondFamily->id,
    ]);
});

test('patients cannot invite transport for a family not linked to them', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $linkedFamilyUser = User::factory()->familyMember()->create();
    $linkedFamily = $linkedFamilyUser->family;
    expect($linkedFamily)->not->toBeNull();
    $linkedFamily->patients()->syncWithoutDetaching([$patient->id]);

    $otherFamilyUser = User::factory()->familyMember()->create();
    $otherFamily = $otherFamilyUser->family;
    expect($otherFamily)->not->toBeNull();

    $startsAt = now()->addWeek();

    $this->actingAs($patientUser)->post(route('patient.appointments.store'), [
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
        'provider_name' => 'City Clinic',
        'street' => 'Main Street',
        'house_number' => '1',
        'postal_code' => '1234 AB',
        'city' => 'Amsterdam',
        'starts_at' => $startsAt->toDateTimeString(),
        'needs_transport' => true,
        'transport_family_ids' => [(int) $otherFamily->id],
        'notes' => null,
        'status' => AppointmentStatus::SCHEDULED->value,
    ])->assertSessionHasErrors('transport_family_ids.0');
});

test('patients can update their appointment', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $response = $this->actingAs($user)->patch(route('patient.appointments.update', $appointment), [
        'status' => AppointmentStatus::CANCELLED->value,
    ]);

    $response->assertRedirect(route('patient.appointments'));

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::CANCELLED);
});

test('patients without linked family cannot persist transport when creating an appointment', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $otherFamilyUser = User::factory()->familyMember()->create();
    $otherFamily = $otherFamilyUser->family;
    expect($otherFamily)->not->toBeNull();

    $startsAt = now()->addWeek();

    $this->actingAs($patientUser)->post(route('patient.appointments.store'), [
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
        'provider_name' => 'City Clinic',
        'street' => 'Main Street',
        'house_number' => '1',
        'postal_code' => '1234 AB',
        'city' => 'Amsterdam',
        'starts_at' => $startsAt->toDateTimeString(),
        'needs_transport' => true,
        'transport_family_ids' => [(int) $otherFamily->id],
        'notes' => null,
        'status' => AppointmentStatus::SCHEDULED->value,
    ])->assertRedirect(route('patient.appointments'));

    $appointment = Appointment::query()->where('patient_id', $patient->id)->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->needs_transport)->toBeFalse();
});

test('patients without linked family have transport cleared when updating an appointment', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->family;
    expect($family)->not->toBeNull();
    $family->patients()->syncWithoutDetaching([$patient->id]);

    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
        'needs_transport' => true,
    ]);

    $family->patients()->detach($patient->id);

    $this->actingAs($patientUser)->patch(route('patient.appointments.update', $appointment), [
        'notes' => 'Updated note.',
    ])->assertRedirect(route('patient.appointments'));

    expect($appointment->fresh()->needs_transport)->toBeFalse();
    expect($appointment->fresh()->notes)->toBe('Updated note.');
});

test('patients can cancel an appointment with optional reason', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)->patch(route('patient.appointments.update', $appointment), [
        'status' => AppointmentStatus::CANCELLED->value,
        'cancellation_reason' => 'Ziek, afspraak verzet door de praktijk.',
    ])->assertRedirect(route('patient.appointments'));

    $fresh = $appointment->fresh();
    expect($fresh->status)->toBe(AppointmentStatus::CANCELLED);
    expect($fresh->cancellation_reason)->toBe('Ziek, afspraak verzet door de praktijk.');
});

test('patients can mark an appointment as done and back to scheduled', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)->patch(route('patient.appointments.update', $appointment), [
        'status' => AppointmentStatus::DONE->value,
    ])->assertRedirect(route('patient.appointments'));

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::DONE);

    $this->actingAs($user)->patch(route('patient.appointments.update', $appointment), [
        'status' => AppointmentStatus::SCHEDULED->value,
        'doctor_visit_summary' => null,
    ])->assertRedirect(route('patient.appointments'));

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::SCHEDULED);
});

test('patients can mark an appointment done with optional visit summary', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)->patch(route('patient.appointments.update', $appointment), [
        'status' => AppointmentStatus::DONE->value,
        'doctor_visit_summary' => 'Follow-up in six months. Tell family all went well.',
    ])->assertRedirect(route('patient.appointments'));

    $fresh = $appointment->fresh();
    expect($fresh->status)->toBe(AppointmentStatus::DONE);
    expect($fresh->doctor_visit_summary)->toBe('Follow-up in six months. Tell family all went well.');
});

test('patients can mark an appointment done without visit summary', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)->patch(route('patient.appointments.update', $appointment), [
        'status' => AppointmentStatus::DONE->value,
        'doctor_visit_summary' => null,
    ])->assertRedirect(route('patient.appointments'));

    $fresh = $appointment->fresh();
    expect($fresh->status)->toBe(AppointmentStatus::DONE);
    expect($fresh->doctor_visit_summary)->toBeNull();
});

test('patients can open the complete appointment page for a scheduled appointment', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)
        ->get(route('patient.appointments.complete', $appointment))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Appointments/Complete')
            ->where('appointment.id', $appointment->id));
});

test('patients are redirected from the complete page when the appointment is not scheduled', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::DONE,
    ]);

    $this->actingAs($user)
        ->get(route('patient.appointments.complete', $appointment))
        ->assertRedirect(route('patient.appointments'));
});

test('patients can open the cancel appointment page for a scheduled appointment', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)
        ->get(route('patient.appointments.cancel', $appointment))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Appointments/Cancel')
            ->where('appointment.id', $appointment->id));
});

test('patients are redirected from the cancel page when the appointment is not scheduled', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::CANCELLED,
    ]);

    $this->actingAs($user)
        ->get(route('patient.appointments.cancel', $appointment))
        ->assertRedirect(route('patient.appointments'));
});

test('patients who save a completed appointment from the outcome flow see the schedule-next prompt', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)->patch(
        route('patient.appointments.update', $appointment).'?outcome_follow_up=done',
        [
            'status' => AppointmentStatus::DONE->value,
            'doctor_visit_summary' => null,
        ],
    )->assertRedirect(route('patient.appointments.complete', $appointment));

    $this->actingAs($user)
        ->get(route('patient.appointments.complete', $appointment))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Appointments/Complete')
            ->where('show_schedule_next_prompt', true));
});

test('patients who save a cancellation from the outcome flow see the schedule-next prompt', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)->patch(
        route('patient.appointments.update', $appointment).'?outcome_follow_up=cancelled',
        [
            'status' => AppointmentStatus::CANCELLED->value,
            'cancellation_reason' => null,
        ],
    )->assertRedirect(route('patient.appointments.cancel', $appointment));

    $this->actingAs($user)
        ->get(route('patient.appointments.cancel', $appointment))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Appointments/Cancel')
            ->where('show_schedule_next_prompt', true));
});

test('outcome follow-up query is ignored when it does not match the saved status', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user)->patch(
        route('patient.appointments.update', $appointment).'?outcome_follow_up=cancelled',
        [
            'status' => AppointmentStatus::DONE->value,
        ],
    )->assertRedirect(route('patient.appointments'));
});

test('patients can open the schedule-next prompt when the outcome is valid', function () {
    $user = User::factory()->patient()->create();

    $this->actingAs($user)
        ->get(route('patient.appointments.schedule-next', ['outcome' => 'done']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Appointments/ScheduleNext')
            ->where('outcome', 'done'));
});

test('patients are redirected from the schedule-next page when the outcome is missing or invalid', function () {
    $user = User::factory()->patient()->create();

    $this->actingAs($user)
        ->get(route('patient.appointments.schedule-next'))
        ->assertRedirect(route('patient.appointments'));

    $this->actingAs($user)
        ->get(route('patient.appointments.schedule-next', ['outcome' => 'scheduled']))
        ->assertRedirect(route('patient.appointments'));
});

test('patient appointments index can request the create dialog to open', function () {
    $user = User::factory()->patient()->create();

    $this->actingAs($user)
        ->get(route('patient.appointments', ['open_create' => 1]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('open_create_dialog', true));
});

test('patients cannot open the complete page for another patients appointment', function () {
    $firstUser = User::factory()->patient()->create();
    $secondUser = User::factory()->patient()->create();

    $appointment = Appointment::factory()->for($secondUser->patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($firstUser)
        ->get(route('patient.appointments.complete', $appointment))
        ->assertForbidden();
});

test('patients can delete their appointment', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create();

    $response = $this->actingAs($user)->delete(route('patient.appointments.destroy', $appointment));

    $response->assertRedirect(route('patient.appointments'));

    $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
});

test('patients can delete a done appointment from the completed tab', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::DONE,
    ]);

    $response = $this->actingAs($user)->delete(route('patient.appointments.destroy', $appointment));

    $response->assertRedirect(route('patient.appointments'));

    $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
});

test('patient appointments index ignores completed view query', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;

    Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);
    Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::DONE,
        'starts_at' => now()->subDays(2),
    ]);
    Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::CANCELLED,
        'starts_at' => now()->subDays(10),
    ]);

    $this->actingAs($user)
        ->get(route('patient.appointments', ['view' => 'completed']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('appointments.data', 1)
            ->where('appointments.meta.total', 1)
            ->where('appointment_view', 'planned')
            ->where('appointment_tab_totals.planned', 1)
            ->where('appointment_tab_totals.completed', 2));
});

test('patients cannot update another patients appointment', function () {
    $firstUser = User::factory()->patient()->create();
    $secondUser = User::factory()->patient()->create();

    $appointment = Appointment::factory()->for($secondUser->patient)->create();

    $response = $this->actingAs($firstUser)->patch(route('patient.appointments.update', $appointment), [
        'status' => AppointmentStatus::DONE->value,
    ]);

    $response->assertForbidden();
});

test('patients cannot mutate a cancelled appointment via update', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::CANCELLED,
        'cancellation_reason' => 'Ziek.',
    ]);

    $this->actingAs($user)->patch(route('patient.appointments.update', $appointment), [
        'status' => AppointmentStatus::SCHEDULED->value,
    ])->assertRedirect(route('patient.appointments'));

    $fresh = $appointment->fresh();
    expect($fresh->status)->toBe(AppointmentStatus::CANCELLED);
    expect($fresh->cancellation_reason)->toBe('Ziek.');
});

test('patients cannot delete another patients appointment', function () {
    $firstUser = User::factory()->patient()->create();
    $secondUser = User::factory()->patient()->create();

    $appointment = Appointment::factory()->for($secondUser->patient)->create();

    $response = $this->actingAs($firstUser)->delete(route('patient.appointments.destroy', $appointment));

    $response->assertForbidden();
});
