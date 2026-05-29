<?php

use App\Models\FamilyInvitation;
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
        ['streak_count' => 0],
    );

    $invitedEmail = 'family-mail-fail-'.uniqid('', true).'@example.com';

    $response = $this->from(route('patient.family'))->actingAs($patientUser)->post(
        route('patient.family.invitations.store'),
        [
            'email' => $invitedEmail,
        ],
    );

    $response->assertSessionHasErrors([
        'email' => trans('family_invitation.flash.mail_failed'),
    ]);

    expect(
        FamilyInvitation::query()
            ->where('patient_id', $patient->id)
            ->whereNull('revoked_at')
            ->whereNull('accepted_at')
            ->exists(),
    )->toBeFalse();
});
