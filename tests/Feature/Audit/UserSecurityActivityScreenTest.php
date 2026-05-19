<?php

use App\Enums\SecurityActivityDescription;
use App\Models\User;
use App\Services\Audit\SecurityActivityLogger;

test('settings security activity section shows the users security log entries', function () {
    $user = User::factory()->patient()->create();

    app(SecurityActivityLogger::class)->record(
        SecurityActivityDescription::AUTH_LOGIN_SUCCEEDED,
        causer: $user,
        properties: ['public_id' => $user->public_id],
    );

    $this->actingAs($user)
        ->get(route('settings.edit', ['section' => 'security-activity']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Edit')
            ->has('securityActivities.data', 1)
            ->where('securityActivities.data.0.description', SecurityActivityDescription::AUTH_LOGIN_SUCCEEDED->value));
});

test('settings security activity section does not show other users entries', function () {
    $user = User::factory()->patient()->create();
    $other = User::factory()->patient()->create();

    app(SecurityActivityLogger::class)->record(
        SecurityActivityDescription::AUTH_LOGOUT,
        causer: $other,
    );

    $this->actingAs($user)
        ->get(route('settings.edit', ['section' => 'security-activity']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Settings/Edit')
            ->where('securityActivities.data', []));
});
