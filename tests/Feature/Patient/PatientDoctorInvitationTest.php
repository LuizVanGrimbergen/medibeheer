<?php

use App\Mail\DoctorInvitationMail;
use App\Models\Doctor;
use App\Models\DoctorInvitation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('patients can send a doctor invitation email', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'doctor-invitee-'.uniqid('', true).'@example.com';

    $response = $this->actingAs($patientUser)->post(route('patient.doctors.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $response->assertRedirect(route('patient.family'));

    Mail::assertSent(DoctorInvitationMail::class, function (DoctorInvitationMail $mail) use ($invitedEmail): bool {
        return $mail->hasTo(User::normalizeEmail($invitedEmail));
    });

    expect(
        DoctorInvitation::query()
            ->where('patient_id', $patient->id)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->exists(),
    )->toBeTrue();
});

test('patients cannot send a second pending doctor invitation to the same email', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'doctor-dup-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.doctors.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $response = $this->actingAs($patientUser)->post(route('patient.doctors.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $response->assertSessionHasErrors('email');

    expect(
        DoctorInvitation::query()
            ->where('patient_id', $patient->id)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->count(),
    )->toBe(1);
});

test('patients can revoke a pending doctor invitation', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'doctor-revoke-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.doctors.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $invitation = DoctorInvitation::query()
        ->where('patient_id', $patient->id)
        ->firstOrFail();

    $response = $this->actingAs($patientUser)->delete(
        route('patient.doctors.invitations.destroy', ['doctorInvitation' => $invitation->public_id]),
    );

    $response->assertRedirect(route('patient.family'));

    expect($invitation->fresh()->revoked_at)->not->toBeNull();
});

test('patients cannot invite a doctor who is already linked', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $doctorUser = User::factory()->doctor()->create();
    $doctor = Doctor::query()->firstOrCreate(['user_id' => $doctorUser->id]);
    $doctor->patients()->syncWithoutDetaching([(int) $patient->id]);

    $response = $this->actingAs($patientUser)->post(route('patient.doctors.invitations.store'), [
        'email' => $doctorUser->email,
    ]);

    $response->assertSessionHasErrors('email');
    Mail::assertNothingSent();
});

test('verified patients can visit the patient family page with doctors section', function () {
    $user = User::factory()->patient()->create();

    $this->actingAs($user)
        ->get(route('patient.family'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Family')
            ->has('family_invitations')
            ->has('doctor_invitations'));
});

test('patient doctors route redirects to family doctors section', function () {
    $user = User::factory()->patient()->create();

    $this->actingAs($user)
        ->get(route('patient.doctors'))
        ->assertRedirect(route('patient.family').'#doctors');
});
