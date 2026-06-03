<?php

use App\Models\DoctorInvitation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('when sending mail fails the invitation is removed and the user sees an error on the email field', function () {
    Mail::shouldReceive('to')
        ->once()
        ->andThrow(new RuntimeException('SMTP connection failed'));

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'doctor-mail-fail-'.uniqid('', true).'@example.com';

    $response = $this->from(route('patient.family'))->actingAs($patientUser)->post(
        route('patient.doctors.invitations.store'),
        [
            'email' => $invitedEmail,
        ],
    );

    $response->assertSessionHasErrors([
        'email' => trans('doctor_invitation.flash.mail_failed'),
    ]);

    expect(
        DoctorInvitation::query()
            ->where('patient_id', $patient->id)
            ->whereNull('revoked_at')
            ->whereNull('accepted_at')
            ->exists(),
    )->toBeFalse();
});
