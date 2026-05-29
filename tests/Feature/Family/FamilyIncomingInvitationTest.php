<?php

use App\Mail\FamilyInvitationAcceptedMail;
use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('family link page lists pending patient invitations for the family member email', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
        ['streak_count' => 0],
    );

    $invitedEmail = 'family-incoming-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $familyUser = User::factory()->familyMember()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($familyUser)->get(route('family.link'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Family/Link')
        ->has('incoming_invitations', 1)
        ->where('incoming_invitations.0.patient_name', $patientUser->name));
});

test('family members can accept an incoming patient invitation from the link page', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
        ['streak_count' => 0],
    );

    $invitedEmail = 'family-incoming-accept-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $invitation = FamilyInvitation::query()
        ->where('patient_id', $patient->id)
        ->firstOrFail();

    $familyUser = User::factory()->familyMember()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($familyUser)->post(
        route('family.invitations.incoming.accept', ['incomingFamilyInvitation' => $invitation->public_id]),
    );

    $response->assertRedirect(route('family.link'));

    Mail::assertSent(FamilyInvitationAcceptedMail::class, function (FamilyInvitationAcceptedMail $mail) use ($patientUser, $familyUser): bool {
        return $mail->hasTo($patientUser->email)
            && $mail->accepterName === $familyUser->name;
    });

    $family = Family::query()->where('user_id', $familyUser->id)->firstOrFail();

    expect($family->patients()->whereKey($patient->id)->exists())->toBeTrue();
});

test('family members can accept invitations from multiple patients', function () {
    Mail::fake();

    $firstPatientUser = User::factory()->patient()->create();
    $firstPatient = Patient::query()->firstOrCreate(
        ['user_id' => $firstPatientUser->id],
        ['streak_count' => 0],
    );

    $secondPatientUser = User::factory()->patient()->create();
    $secondPatient = Patient::query()->firstOrCreate(
        ['user_id' => $secondPatientUser->id],
        ['streak_count' => 0],
    );

    $invitedEmail = 'family-multi-'.uniqid('', true).'@example.com';

    $this->actingAs($firstPatientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $this->actingAs($secondPatientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $familyUser = User::factory()->familyMember()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    $invitations = FamilyInvitation::query()
        ->orderBy('patient_id')
        ->get();

    expect($invitations)->toHaveCount(2);

    foreach ($invitations as $invitation) {
        $this->actingAs($familyUser)
            ->post(route('family.invitations.incoming.accept', ['incomingFamilyInvitation' => $invitation->public_id]))
            ->assertRedirect(route('family.link'));
    }

    $family = Family::query()->where('user_id', $familyUser->id)->firstOrFail();

    expect($family->patients()->whereKey($firstPatient->id)->exists())->toBeTrue();
    expect($family->patients()->whereKey($secondPatient->id)->exists())->toBeTrue();
});

test('family link page omits invitations for patients already linked', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
        ['streak_count' => 0],
    );

    $invitedEmail = 'family-incoming-linked-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $familyUser = User::factory()->familyMember()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    $family = Family::query()->firstOrCreate(['user_id' => $familyUser->id]);
    $family->patients()->syncWithoutDetaching([(int) $patient->id]);

    $this->actingAs($familyUser)
        ->get(route('family.link'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Link')
            ->has('incoming_invitations', 0));
});

test('family members cannot accept an invitation sent to another email address', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
        ['streak_count' => 0],
    );

    $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => 'invited-'.uniqid('', true).'@example.com',
    ]);

    $invitation = FamilyInvitation::query()
        ->where('patient_id', $patient->id)
        ->firstOrFail();

    $familyUser = User::factory()->familyMember()->create([
        'email' => 'other-'.uniqid('', true).'@example.com',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($familyUser)
        ->post(route('family.invitations.incoming.accept', ['incomingFamilyInvitation' => $invitation->public_id]))
        ->assertNotFound();
});

test('family members cannot accept an invitation using a numeric id', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
        ['streak_count' => 0],
    );

    $invitedEmail = 'family-numeric-id-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $invitation = FamilyInvitation::query()->firstOrFail();

    $familyUser = User::factory()->familyMember()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($familyUser)
        ->post(route('family.invitations.incoming.accept', ['incomingFamilyInvitation' => (string) $invitation->id]))
        ->assertNotFound();
});

test('family invitation accepted mail points to the patient family page', function () {
    $mail = new FamilyInvitationAcceptedMail(
        accepterName: 'Marc Maas',
    );

    $html = $mail->render();

    expect($html)->toContain(route('patient.family', absolute: true));
});
