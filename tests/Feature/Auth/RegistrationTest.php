<?php

use App\Models\Doctor;
use App\Models\Family;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    Notification::fake();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'patient',
        'password' => 'Qw7!mR2#xP9@tL4$',
        'password_confirmation' => 'Qw7!mR2#xP9@tL4$',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));
    Notification::assertSentTo(auth()->user(), VerifyEmailNotification::class);

    /** @var User $registeredUser */
    $registeredUser = auth()->user();
    expect(Patient::query()->where('user_id', $registeredUser->id)->exists())->toBeTrue();
});

test('new users can register with uppercase and padded email input', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => '  TEST@EXAMPLE.COM  ',
        'role' => 'patient',
        'password' => 'Qw7!mR2#xP9@tL4$',
        'password_confirmation' => 'Qw7!mR2#xP9@tL4$',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));
});

test('new users can not register with duplicate email ignoring case and whitespace', function () {
    $this->post('/register', [
        'name' => 'First User',
        'email' => 'test@example.com',
        'role' => 'patient',
        'password' => 'Qw7!mR2#xP9@tL4$',
        'password_confirmation' => 'Qw7!mR2#xP9@tL4$',
    ]);

    auth()->logout();

    $response = $this->post('/register', [
        'name' => 'Second User',
        'email' => '  TEST@EXAMPLE.COM ',
        'role' => 'doctor',
        'password' => 'Qw7!mR2#xP9@tL4$',
        'password_confirmation' => 'Qw7!mR2#xP9@tL4$',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registering as family member creates a family profile and not a patient profile', function () {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Family User',
        'email' => 'family.profile@example.com',
        'role' => 'family_member',
        'password' => 'Qw7!mR2#xP9@tL4$',
        'password_confirmation' => 'Qw7!mR2#xP9@tL4$',
    ]);

    $this->assertAuthenticated();

    /** @var User $registeredUser */
    $registeredUser = auth()->user();
    expect(Patient::query()->where('user_id', $registeredUser->id)->exists())->toBeFalse();
    expect(Family::query()->where('user_id', $registeredUser->id)->exists())->toBeTrue();
});

test('registering as doctor does not create a patient profile', function () {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Doctor User',
        'email' => 'doctor.profile@example.com',
        'role' => 'doctor',
        'password' => 'Qw7!mR2#xP9@tL4$',
        'password_confirmation' => 'Qw7!mR2#xP9@tL4$',
    ]);

    $this->assertAuthenticated();

    /** @var User $registeredUser */
    $registeredUser = auth()->user();
    expect(Patient::query()->where('user_id', $registeredUser->id)->exists())->toBeFalse();
    expect(Doctor::query()->where('user_id', $registeredUser->id)->exists())->toBeFalse();
});
