<?php

use App\Mail\FamilyInvitationMail;
use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('patients can send a family invitation email with inertia ajax headers', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'family-invitee-'.uniqid('', true).'@example.com';

    $response = $this->actingAs($patientUser)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->post(route('patient.family.invitations.store'), [
            'email' => $invitedEmail,
        ]);

    $response->assertRedirect(route('patient.family'));

    Mail::assertSent(FamilyInvitationMail::class, function (FamilyInvitationMail $mail) use ($invitedEmail): bool {
        return $mail->hasTo(User::normalizeEmail($invitedEmail));
    });

    expect(
        FamilyInvitation::query()
            ->where('patient_id', $patient->id)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->exists(),
    )->toBeTrue();
});

test('unverified patients are redirected when posting a family invitation as an inertia request', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->unverified()->create();

    Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'family-invitee-'.uniqid('', true).'@example.com';

    $response = $this->actingAs($patientUser)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->post(route('patient.family.invitations.store'), [
            'email' => $invitedEmail,
        ]);

    $response->assertRedirect(route('verification.notice', absolute: false));
    Mail::assertNothingSent();
});

test('patients cannot send a second pending invitation to the same email address', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'family-dup-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    Mail::assertSent(FamilyInvitationMail::class);

    $response = $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $response->assertSessionHasErrors('email');
    Mail::assertSent(FamilyInvitationMail::class, 1);

    expect(
        FamilyInvitation::query()
            ->where('patient_id', $patient->id)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->count(),
    )->toBe(1);
});

test('patients can invite the same email again after revoking the pending invitation', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'family-reinvite-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $invitation = FamilyInvitation::query()
        ->where('patient_id', $patient->id)
        ->firstOrFail();

    $this->actingAs($patientUser)->delete(
        route('patient.family.invitations.destroy', $invitation),
    );

    $response = $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $response->assertRedirect(route('patient.family'));
    Mail::assertSent(FamilyInvitationMail::class, 2);
});

test('patients cannot invite a family member who is already linked', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = User::factory()->familyMember()->create();
    $family = Family::query()->firstOrCreate(['user_id' => $familyUser->id]);
    $family->patients()->syncWithoutDetaching([(int) $patient->id]);

    $response = $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $familyUser->email,
    ]);

    $response->assertSessionHasErrors('email');
    Mail::assertNothingSent();
});

test('patients cannot invite their own email address', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();

    Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $response = $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $patientUser->email,
    ]);

    $response->assertSessionHasErrors('email');
    Mail::assertNothingSent();
});

test('patients cannot revoke another patients family invitation', function () {
    $patientUserA = User::factory()->patient()->create();
    $patientA = $patientUserA->patient;
    expect($patientA)->not->toBeNull();

    $patientUserB = User::factory()->patient()->create();
    $patientB = $patientUserB->patient;
    expect($patientB)->not->toBeNull();

    $invitation = FamilyInvitation::factory()->create([
        'patient_id' => $patientA->id,
    ]);

    $this->actingAs($patientUserB)
        ->delete(route('patient.family.invitations.destroy', $invitation))
        ->assertNotFound();

    expect($invitation->fresh()->revoked_at)->toBeNull();
});

test('patient family page lists invited email on pending family invitations', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'family-visible-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $this->actingAs($patientUser)
        ->get(route('patient.family'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Family')
            ->has('family_invitations', 1)
            ->where('family_invitations.0.invited_email', User::normalizeEmail($invitedEmail)));
});

test('patients can revoke a pending invitation', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $invitedEmail = 'family-revoke-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $invitation = FamilyInvitation::query()
        ->where('patient_id', $patient->id)
        ->firstOrFail();

    $response = $this->actingAs($patientUser)->delete(
        route('patient.family.invitations.destroy', $invitation),
    );

    $response->assertRedirect(route('patient.family'));

    expect($invitation->fresh()->revoked_at)->not->toBeNull();
});
