<?php

use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Notification;

test('profile page is displayed', function () {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->get('/settings');

    $response->assertOk();
});

test('profile information can be updated', function () {
    /** @var User $user */
    $user = User::factory()->create();

    Notification::fake();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->patch('/settings', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'current_password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    /** @var User $user */
    $user = User::factory()->create();

    Notification::fake();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->patch('/settings', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings');

    $this->assertNotNull($user->refresh()->email_verified_at);

    Notification::assertNotSentTo($user, VerifyEmailNotification::class);
});

test('profile email cannot be changed without confirming current password', function () {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->from('/settings')
        ->patch('/settings', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect('/settings');
});

test('profile email can be updated with uppercase and padded input', function () {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->patch('/settings', [
            'name' => 'Test User',
            'email' => '  TEST@EXAMPLE.COM  ',
            'current_password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings');

    $this->assertSame('test@example.com', $user->refresh()->email);
});

test('user can delete their account', function () {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->delete('/settings', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var TestCase $this */
    $response = $this
        ->actingAs($user)
        ->from('/settings')
        ->delete('/settings', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect('/settings');

    $this->assertNotNull($user->fresh());
});
