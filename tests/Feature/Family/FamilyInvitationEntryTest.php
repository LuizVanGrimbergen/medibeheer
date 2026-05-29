<?php

use App\Mail\FamilyInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\URL;

test('family invitation entry sends guests to family member registration with intended link page', function () {
    $response = $this->get(route('family.invitation.entry'));

    $response->assertRedirect(route('register', ['role' => 'family_member']));
    expect(session('url.intended'))->toBe(route('family.link'));
});

test('family invitation entry redirects verified family members to the link page', function () {
    $user = User::factory()->familyMember()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('family.invitation.entry'))
        ->assertRedirect(route('family.link'));
});

test('family invitation entry redirects unverified family members to email verification', function () {
    $user = User::factory()->familyMember()->unverified()->create();

    $response = $this->actingAs($user)->get(route('family.invitation.entry'));

    $response->assertRedirect(route('verification.notice'));
    expect(session('url.intended'))->toBe(route('family.link'));
});

test('family invitation entry redirects patients away with an error instead of forbidden', function () {
    $user = User::factory()->patient()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('family.invitation.entry'))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error', trans('family_invitation.entry.wrong_account'));
});

test('login continues to email verification link for unverified users', function () {
    $user = User::factory()->familyMember()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->get($verificationUrl)->assertRedirect(route('login'));

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'family_member',
    ])->assertRedirect($verificationUrl);

    $this->get($verificationUrl)
        ->assertRedirect(route('family.overview', absolute: false).'?verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('family invitation mail points to the public entry route', function () {
    $mail = new FamilyInvitationMail(
        expiresAt: now()->addDays(14),
        patientName: 'Sophie Maas',
    );

    $html = $mail->render();

    expect($html)->toContain(route('family.invitation.entry', absolute: true));
});
