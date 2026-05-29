<?php

use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('reset password request returns generic status for unknown email', function () {
    Notification::fake();

    $response = $this->post('/forgot-password', ['email' => 'unknown@example.com']);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', trans('passwords.sent'));
});

test('reset password request keeps a generic status when requested too quickly for same user', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertSessionHas('status', trans('passwords.sent'));

    $response = $this->post('/forgot-password', ['email' => $user->email]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', trans('passwords.sent'));
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => User::hashEmail($user->email),
            'password' => 'ResetPassword!1',
            'password_confirmation' => 'ResetPassword!1',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('password can not be reset with invalid email hash format', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => 'invalid-hash',
            'password' => 'ResetPassword!1',
            'password_confirmation' => 'ResetPassword!1',
        ]);

        $response->assertSessionHasErrors('email');

        return true;
    });
});
