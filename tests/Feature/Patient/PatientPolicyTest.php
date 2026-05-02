<?php

use App\Models\User;

test('patients can view their own patient profile', function () {
    $user = User::factory()->patient()->create();

    expect($user->patient)->not->toBeNull();
    expect($user->can('view', $user->patient))->toBeTrue();
});

test('patients cannot view another users patient profile', function () {
    $firstUser = User::factory()->patient()->create();
    $secondUser = User::factory()->patient()->create();

    expect($firstUser->can('view', $secondUser->patient))->toBeFalse();
});
