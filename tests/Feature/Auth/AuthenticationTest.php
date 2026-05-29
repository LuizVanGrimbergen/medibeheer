<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('doctors are redirected to the doctor dashboard after login', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'doctor',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('doctor.dashboard', absolute: false));
});

test('family members are redirected to the family overview after login', function () {
    $user = User::factory()->familyMember()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'family_member',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('family.overview', absolute: false));
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'patient',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('patient.dashboard', absolute: false));
});

test('inertia login uses a location visit after successful authentication', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this
        ->withHeader('X-Inertia', 'true')
        ->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'patient',
        ]);

    $this->assertAuthenticated();
    $response->assertStatus(409);
    $response->assertHeader('X-Inertia-Location', route('patient.dashboard', absolute: false));
});

test('unverified users are redirected to email verification notice after login', function () {
    $user = User::factory()->unverified()->create(['role' => 'patient']);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'patient',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'role' => 'patient',
    ]);

    $this->assertGuest();
});

test('users can not authenticate with mismatched role', function () {
    $user = User::factory()->create(['role' => 'doctor']);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'patient',
    ]);

    $this->assertGuest();
});

test('users can authenticate with uppercase and padded email input', function () {
    $user = User::factory()->create(['role' => 'patient']);

    $response = $this->post('/login', [
        'email' => '  '.strtoupper($user->email).'  ',
        'password' => 'password',
        'role' => 'patient',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('patient.dashboard', absolute: false));
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('users can authenticate when email hash was generated with previous key', function () {
    config()->set('app.email_hash_key', 'current-email-hash-key');
    config()->set('app.email_hash_previous_keys', ['previous-email-hash-key']);

    $user = User::factory()->create(['role' => 'patient']);
    $legacyEmailHash = hash_hmac('sha256', $user->email, 'previous-email-hash-key');

    DB::table('users')
        ->where('id', $user->id)
        ->update(['email_hash' => $legacyEmailHash]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'patient',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('patient.dashboard', absolute: false));
});

test('authenticated users can switch account via login endpoint', function () {
    $firstUser = User::factory()->create(['role' => 'patient']);
    $secondUser = User::factory()->create(['role' => 'doctor']);

    $response = $this
        ->actingAs($firstUser)
        ->post('/login', [
            'email' => $secondUser->email,
            'password' => 'password',
            'role' => 'doctor',
        ]);

    $this->assertAuthenticatedAs($secondUser);
    $response->assertRedirect(route('doctor.dashboard', absolute: false));
});

test('failed re-login logs out the previous authenticated user', function () {
    $firstUser = User::factory()->create(['role' => 'patient']);
    $secondUser = User::factory()->create(['role' => 'doctor']);

    $response = $this
        ->actingAs($firstUser)
        ->from('/login')
        ->post('/login', [
            'email' => $secondUser->email,
            'password' => 'wrong-password',
            'role' => 'doctor',
        ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
