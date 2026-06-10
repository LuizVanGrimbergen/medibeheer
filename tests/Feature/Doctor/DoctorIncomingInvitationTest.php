<?php

use App\Mail\DoctorInvitationAcceptedMail;
use App\Models\Doctor;
use App\Models\DoctorInvitation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('doctor patients page lists pending patient invitations for the doctor email', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'doctor-incoming-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.doctors.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $doctorUser = User::factory()->doctor()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($doctorUser)->get(route('doctor.patients'));

    $response->assertOk();
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->component('Doctor/Patients/Index')
        ->has('incoming_invitations', 1)
        ->where('incoming_invitations.0.patient_name', $patientUser->name)));
});

test('doctors can accept an incoming patient invitation from the patients page', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'doctor-incoming-accept-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.doctors.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $invitation = DoctorInvitation::query()
        ->where('patient_id', $patient->id)
        ->firstOrFail();

    $doctorUser = User::factory()->doctor()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($doctorUser)->post(
        route('doctor.invitations.incoming.accept', ['incomingDoctorInvitation' => $invitation->public_id]),
    );

    $response->assertRedirect(route('doctor.patients'));

    Mail::assertQueued(DoctorInvitationAcceptedMail::class, function (DoctorInvitationAcceptedMail $mail) use ($patientUser, $doctorUser): bool {
        return $mail->hasTo($patientUser->email)
            && $mail->accepterName === $doctorUser->name;
    });

    $doctor = Doctor::query()->where('user_id', $doctorUser->id)->firstOrFail();

    expect($doctor->patients()->whereKey($patient->id)->exists())->toBeTrue();
});

test('doctor patients page omits invitations for patients already linked', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'doctor-incoming-linked-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.doctors.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $doctorUser = User::factory()->doctor()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    $doctor = Doctor::query()->firstOrCreate(['user_id' => $doctorUser->id]);
    $doctor->patients()->syncWithoutDetaching([(int) $patient->id]);

    $this->actingAs($doctorUser)
        ->get(route('doctor.patients'))
        ->assertOk()
        ->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
            ->component('Doctor/Patients/Index')
            ->has('incoming_invitations', 0)));
});

test('doctors cannot accept an invitation sent to another email address', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $this->actingAs($patientUser)->post(route('patient.doctors.invitations.store'), [
        'email' => 'invited-'.uniqid('', true).'@example.com',
    ]);

    $invitation = DoctorInvitation::query()
        ->where('patient_id', $patient->id)
        ->firstOrFail();

    $doctorUser = User::factory()->doctor()->create([
        'email' => 'other-'.uniqid('', true).'@example.com',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($doctorUser)
        ->post(route('doctor.invitations.incoming.accept', ['incomingDoctorInvitation' => $invitation->public_id]))
        ->assertNotFound();
});

test('doctors cannot accept an invitation using a numeric id', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'doctor-numeric-id-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.doctors.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $invitation = DoctorInvitation::query()->firstOrFail();

    $doctorUser = User::factory()->doctor()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($doctorUser)
        ->post(route('doctor.invitations.incoming.accept', ['incomingDoctorInvitation' => (string) $invitation->id]))
        ->assertNotFound();
});

test('doctor invitation accepted mail points to the patient doctors page', function () {
    $mail = new DoctorInvitationAcceptedMail(
        accepterName: 'Dr. Jan Maas',
    );

    $html = $mail->render();

    expect($html)->toContain(route('patient.doctors', absolute: true));
});
