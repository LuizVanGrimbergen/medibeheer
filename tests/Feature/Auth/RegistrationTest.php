<?php

use App\Models\Doctor;
use App\Models\Family;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;

/** @return array<string, mixed> */
function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'patient',
        'password' => 'Qw7!mR2#xP9@tL4$',
        'password_confirmation' => 'Qw7!mR2#xP9@tL4$',
        'accepted_privacy_policy' => true,
        'accepted_health_data_processing' => true,
    ], $overrides);
}

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    Notification::fake();

    $response = $this->post('/register', registrationPayload());

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));
    Notification::assertSentTo(auth()->user(), VerifyEmailNotification::class);

    /** @var User $registeredUser */
    $registeredUser = auth()->user();
    expect(Patient::query()->where('user_id', $registeredUser->id)->exists())->toBeTrue();
});

test('new users can register with uppercase and padded email input', function () {
    $response = $this->post('/register', registrationPayload([
        'email' => '  TEST@EXAMPLE.COM  ',
    ]));

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));
});

test('new users can not register with duplicate email ignoring case and whitespace', function () {
    $this->post('/register', registrationPayload([
        'name' => 'First User',
    ]));

    auth()->logout();

    $response = $this->post('/register', registrationPayload([
        'name' => 'Second User',
        'email' => '  TEST@EXAMPLE.COM ',
        'role' => 'doctor',
    ]));

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registering as family member creates a family profile and not a patient profile', function () {
    Notification::fake();

    $this->post('/register', registrationPayload([
        'name' => 'Family User',
        'email' => 'family.profile@example.com',
        'role' => 'family_member',
    ]));

    $this->assertAuthenticated();

    /** @var User $registeredUser */
    $registeredUser = auth()->user();
    expect(Patient::query()->where('user_id', $registeredUser->id)->exists())->toBeFalse();
    expect(Family::query()->where('user_id', $registeredUser->id)->exists())->toBeTrue();
});

test('registering as doctor does not create a patient profile', function () {
    Notification::fake();

    $this->post('/register', registrationPayload([
        'name' => 'Doctor User',
        'email' => 'doctor.profile@example.com',
        'role' => 'doctor',
    ]));

    $this->assertAuthenticated();

    /** @var User $registeredUser */
    $registeredUser = auth()->user();
    expect(Patient::query()->where('user_id', $registeredUser->id)->exists())->toBeFalse();
    expect(Doctor::query()->where('user_id', $registeredUser->id)->exists())->toBeFalse();
});
