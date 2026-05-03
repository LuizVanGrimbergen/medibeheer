<?php

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Models\User;

test('patients see appointments on the appointments page', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    Appointment::factory()->for($patient)->count(2)->create();

    $response = $this->actingAs($user)->get(route('patient.appointments'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Appointments');
    $response->assertInertia(fn ($page) => $page
        ->has('appointments', 2));
});

test('patients can create an appointment', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $startsAt = now()->addWeek();

    $response = $this->actingAs($user)->post(route('patient.appointments.store'), [
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
        'provider_name' => 'City Clinic',
        'address' => 'Main Street 1, Amsterdam',
        'starts_at' => $startsAt->toDateTimeString(),
        'notes' => 'Bring ID.',
        'status' => AppointmentStatus::SCHEDULED->value,
    ]);

    $response->assertRedirect(route('patient.appointments'));

    $this->assertDatabaseHas('appointments', [
        'patient_id' => $patient->id,
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
        'status' => AppointmentStatus::SCHEDULED->value,
    ]);

    $appointment = Appointment::query()->where('patient_id', $patient->id)->first();
    expect($appointment)->not->toBeNull();
    expect($appointment->provider_name)->toBe('City Clinic');
    expect($appointment->address)->toBe('Main Street 1, Amsterdam');
    expect($appointment->notes)->toBe('Bring ID.');
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

test('patients can delete their appointment', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    $appointment = Appointment::factory()->for($patient)->create();

    $response = $this->actingAs($user)->delete(route('patient.appointments.destroy', $appointment));

    $response->assertRedirect(route('patient.appointments'));

    $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
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

test('patients cannot delete another patients appointment', function () {
    $firstUser = User::factory()->patient()->create();
    $secondUser = User::factory()->patient()->create();

    $appointment = Appointment::factory()->for($secondUser->patient)->create();

    $response = $this->actingAs($firstUser)->delete(route('patient.appointments.destroy', $appointment));

    $response->assertForbidden();
});
