<?php

use App\Mail\DoctorInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\URL;

test('doctor invitation entry sends guests to doctor registration with intended patients page', function () {
    $response = $this->get(route('doctor.invitation.entry'));

    $response->assertRedirect(route('register', ['role' => 'doctor']));
    expect(session('url.intended'))->toBe(route('doctor.patients'));
});

test('doctor invitation entry redirects verified doctors to the patients page', function () {
    $user = User::factory()->doctor()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('doctor.invitation.entry'))
        ->assertRedirect(route('doctor.patients'));
});

test('doctor invitation entry redirects unverified doctors to email verification', function () {
    $user = User::factory()->doctor()->unverified()->create();

    $response = $this->actingAs($user)->get(route('doctor.invitation.entry'));

    $response->assertRedirect(route('verification.notice'));
    expect(session('url.intended'))->toBe(route('doctor.patients'));
});

test('doctor invitation entry redirects family members away with an error instead of forbidden', function () {
    $user = User::factory()->familyMember()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('doctor.invitation.entry'))
        ->assertRedirect(route('home'))
        ->assertSessionHas('error', trans('doctor_invitation.entry.wrong_account'));
});

test('email verification link verifies guest doctors and logs them in', function () {
    $user = User::factory()->doctor()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->assertGuest();

    $response = $this->get($verificationUrl);

    $response->assertRedirect(route('doctor.dashboard', absolute: false).'?verified=1');
    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('doctor invitation mail points to the public entry route', function () {
    $mail = new DoctorInvitationMail(
        expiresAt: now()->addDays(14),
        patientName: 'Sophie Maas',
    );

    $html = $mail->render();

    expect($html)->toContain(route('doctor.invitation.entry', absolute: true));
});
