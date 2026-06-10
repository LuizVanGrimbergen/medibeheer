<?php

use App\Enums\UserRole;
use App\Http\Controllers\Auth\ResolveSelectedRole;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;

test('user role encrypts and decrypts transport tokens', function () {
    foreach (UserRole::cases() as $role) {
        $token = $role->encryptForTransport();

        expect($token)->not->toBe($role->value);
        expect(UserRole::tryFromEncryptedTransport($token))->toBe($role);
    }
});

test('user role ignores plain text transport tokens', function () {
    expect(UserRole::tryFromEncryptedTransport('patient'))->toBeNull();
});

test('user role ignores invalid transport tokens', function () {
    expect(UserRole::tryFromEncryptedTransport('not-a-valid-token'))->toBeNull();
});

test('login page resolves selected role from encrypted query parameter', function () {
    $token = UserRole::DOCTOR->encryptForTransport();

    $response = $this->get(route('login', ['role' => $token]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/Login/Index')
        ->where('selectedRole', 'doctor')
        ->has('roleTokens.patient')
        ->has('roleTokens.doctor')
        ->has('roleTokens.family_member'));
});

test('register page ignores plain role query parameter', function () {
    $response = $this->get(route('register', ['role' => 'patient']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Auth/Register/Index')
        ->where('selectedRole', null));
});

test('resolve selected role decrypts encrypted tokens', function () {
    $request = Request::create('/login', 'GET', [
        'role' => UserRole::FAMILY_MEMBER->encryptForTransport(),
    ]);

    expect(app(ResolveSelectedRole::class)($request))->toBe('family_member');
});

test('login request redirect url uses encrypted role token', function () {
    $request = LoginRequest::create('/login', 'POST', [
        'email' => 'test@example.com',
        'password' => 'secret',
        'role' => 'doctor',
    ]);

    $method = new ReflectionMethod(LoginRequest::class, 'getRedirectUrl');
    $url = $method->invoke($request);

    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
    expect(UserRole::tryFromEncryptedTransport($query['role'] ?? null))->toBe(UserRole::DOCTOR);
});
