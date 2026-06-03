<?php

use App\Models\Doctor;
use App\Models\Family;
use App\Models\Patient;
use App\Models\User;

test('patients can unlink a linked family member', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $familyUser = User::factory()->familyMember()->create();
    $family = Family::query()->firstOrCreate(['user_id' => $familyUser->id]);
    $family->patients()->syncWithoutDetaching([(int) $patient->id]);

    $response = $this->actingAs($patientUser)->delete(
        route('patient.family.members.destroy', ['linkedFamilyMember' => $familyUser->public_id]),
    );

    $response->assertRedirect(route('patient.family'));

    expect($family->fresh()->patients()->whereKey($patient->id)->exists())->toBeFalse();
});

test('patients can unlink a linked doctor', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $doctorUser = User::factory()->doctor()->create();
    $doctor = Doctor::query()->firstOrCreate(['user_id' => $doctorUser->id]);
    $patient->doctors()->syncWithoutDetaching([(int) $doctor->id]);

    $response = $this->actingAs($patientUser)->delete(
        route('patient.doctors.links.destroy', ['linkedDoctor' => $doctorUser->public_id]),
    );

    $response->assertRedirect(route('patient.family'));

    expect($patient->fresh()->doctors()->whereKey($doctor->id)->exists())->toBeFalse();
});

test('patients cannot unlink a family member linked to another patient', function () {
    $patientUserA = User::factory()->patient()->create();
    $patientA = Patient::query()->firstOrCreate(
        ['user_id' => $patientUserA->id],
    );

    $patientUserB = User::factory()->patient()->create();
    $patientB = Patient::query()->firstOrCreate(
        ['user_id' => $patientUserB->id],
    );

    $familyUser = User::factory()->familyMember()->create();
    $family = Family::query()->firstOrCreate(['user_id' => $familyUser->id]);
    $family->patients()->syncWithoutDetaching([(int) $patientB->id]);

    $this->actingAs($patientUserA)
        ->delete(route('patient.family.members.destroy', ['linkedFamilyMember' => $familyUser->public_id]))
        ->assertNotFound();

    expect($family->fresh()->patients()->whereKey($patientB->id)->exists())->toBeTrue();
    expect($family->fresh()->patients()->whereKey($patientA->id)->exists())->toBeFalse();
});

test('patient family page includes linked family members and doctors with unlink urls', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = Patient::query()->firstOrCreate(
        ['user_id' => $patientUser->id],
    );

    $familyUser = User::factory()->familyMember()->create(['name' => 'Linked Family']);
    $family = Family::query()->firstOrCreate(['user_id' => $familyUser->id]);
    $family->patients()->syncWithoutDetaching([(int) $patient->id]);

    $doctorUser = User::factory()->doctor()->create(['name' => 'Linked Doctor']);
    $doctor = Doctor::query()->firstOrCreate(['user_id' => $doctorUser->id]);
    $patient->doctors()->syncWithoutDetaching([(int) $doctor->id]);

    $this->actingAs($patientUser)
        ->get(route('patient.family'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Family')
            ->has('linked_family_members', 1)
            ->where('linked_family_members.0.name', 'Linked Family')
            ->has('linked_doctors', 1)
            ->where('linked_doctors.0.name', 'Linked Doctor'));
});
