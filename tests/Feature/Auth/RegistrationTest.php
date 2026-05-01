<?php

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
