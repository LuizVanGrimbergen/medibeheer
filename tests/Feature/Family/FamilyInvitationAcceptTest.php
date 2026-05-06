<?php

use App\Mail\FamilyInvitationMail;
use App\Models\Family;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('family members can accept a valid invitation code', function () {
    Mail::fake();

    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
        ['streak_count' => 0],
    );

    $invitedEmail = 'family-accept-'.uniqid('', true).'@example.com';

    $this->actingAs($patientUser)->post(route('patient.family.invitations.store'), [
        'email' => $invitedEmail,
    ]);

    $plainToken = null;

    Mail::assertSent(FamilyInvitationMail::class, function (FamilyInvitationMail $mail) use (&$plainToken): bool {
        $plainToken = $mail->plainToken;

        return true;
    });

    expect($plainToken)->not->toBeNull();

    $familyUser = User::factory()->familyMember()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($familyUser)->post(route('family.invitations.accept'), [
        'code' => $plainToken,
    ]);

    $response->assertRedirect(route('family.overview'));

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

    Mail::assertSent(FamilyInvitationMail::class, 2);

    $tokens = Mail::sent(FamilyInvitationMail::class)
        ->map(fn (FamilyInvitationMail $mail): string => $mail->plainToken)
        ->values()
        ->all();

    $familyUser = User::factory()->familyMember()->create([
        'email' => $invitedEmail,
        'email_verified_at' => now(),
    ]);

    foreach ($tokens as $plainToken) {
        $this->actingAs($familyUser)->post(route('family.invitations.accept'), [
            'code' => $plainToken,
        ])->assertRedirect(route('family.overview'));
    }

    $family = Family::query()->where('user_id', $familyUser->id)->firstOrFail();

    expect($family->patients()->whereKey($firstPatient->id)->exists())->toBeTrue();
    expect($family->patients()->whereKey($secondPatient->id)->exists())->toBeTrue();
});

test('family members receive a generic validation error for an invalid code', function () {
    $familyUser = User::factory()->familyMember()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($familyUser)->post(route('family.invitations.accept'), [
        'code' => str_repeat('a', 40),
    ]);

    $response->assertSessionHasErrors('code');
});
