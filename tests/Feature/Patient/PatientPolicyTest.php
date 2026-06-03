<?php

use App\Models\Family;
use App\Models\Patient;
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

test('linked family members can view the patient profile read only', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $familyUser = User::factory()->familyMember()->create();
    $family = Family::query()->firstOrCreate(
        ['user_id' => $familyUser->id],
    );
    $family->patients()->syncWithoutDetaching([$patient->id]);

    expect($familyUser->can('view', $patient))->toBeTrue();
    expect($familyUser->can('update', $patient))->toBeFalse();
});

test('family members without a patient link cannot view that patient profile', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $familyUser = User::factory()->familyMember()->create();
    Family::query()->firstOrCreate(
        ['user_id' => $familyUser->id],
    );

    expect($familyUser->can('view', $patient))->toBeFalse();
});
