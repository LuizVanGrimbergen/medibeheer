<?php

use App\Models\User;

test('family members can view their own family profile', function () {
    $user = User::factory()->familyMember()->create();

    expect($user->family)->not->toBeNull();
    expect($user->can('view', $user->family))->toBeTrue();
});

test('family members cannot view another users family profile', function () {
    $firstUser = User::factory()->familyMember()->create();
    $secondUser = User::factory()->familyMember()->create();

    expect($firstUser->can('view', $secondUser->family))->toBeFalse();
});

test('family members cannot update their family profile through authorization', function () {
    $user = User::factory()->familyMember()->create();

    expect($user->family)->not->toBeNull();
    expect($user->can('update', $user->family))->toBeFalse();
});
