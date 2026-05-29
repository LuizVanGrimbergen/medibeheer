<?php

use App\Models\Medication;
use App\Models\User;

test('linked family members can update medication stock through policy', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();
    $family->patients()->attach($patient->id);

    expect($familyUser->can('updateStock', $medication))->toBeTrue();
    expect($familyUser->can('update', $medication))->toBeFalse();
});

test('family members not linked to patient cannot update medication stock', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();
    $familyUser = User::factory()->familyMember()->create();

    expect($familyUser->can('updateStock', $medication))->toBeFalse();
});
