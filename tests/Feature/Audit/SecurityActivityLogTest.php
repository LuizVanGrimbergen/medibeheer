<?php

use App\Enums\SecurityActivityDescription;
use App\Models\User;
use App\Services\Audit\ActivityLogName;
use Spatie\Activitylog\Models\Activity;

test('successful login records a security activity', function () {
    $user = User::factory()->patient()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'patient',
    ]);

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::SECURITY)
        ->where('description', SecurityActivityDescription::AUTH_LOGIN_SUCCEEDED->value)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id)
        ->and($activity->properties->get('role'))->toBe('patient')
        ->and($activity->properties->get('public_id'))->toBe($user->public_id);
});

test('failed login records a security activity without causer', function () {
    $this->post('/login', [
        'email' => 'unknown@example.com',
        'password' => 'password',
        'role' => 'patient',
    ]);

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::SECURITY)
        ->where('description', SecurityActivityDescription::AUTH_LOGIN_FAILED->value)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBeNull()
        ->and($activity->properties->get('role'))->toBe('patient');
});

test('logout records a security activity', function () {
    $user = User::factory()->patient()->create();

    $this->actingAs($user)->post(route('logout'));

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::SECURITY)
        ->where('description', SecurityActivityDescription::AUTH_LOGOUT->value)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id);
});

test('password update records a security activity', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->from('/settings')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'Jk8@vN5#qR3!zT1$',
            'password_confirmation' => 'Jk8@vN5#qR3!zT1$',
        ])
        ->assertSessionHasNoErrors();

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::SECURITY)
        ->where('description', SecurityActivityDescription::AUTH_PASSWORD_UPDATED->value)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id)
        ->and($activity->subject_id)->toBe($user->id);
});
