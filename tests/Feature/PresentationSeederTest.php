<?php

use App\Models\Family;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MedicationSeeder;
use Database\Seeders\PresentationSeeder;

test('presentation medication seeder creates metformine at 15:30 and magnesium at 21:00', function () {
    $user = User::factory()->patient()->create([
        'email' => 'presentation.patient@voorbeeld.nl',
    ]);
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $family = Family::factory()->create();

    (new MedicationSeeder)->run($patient, $family, presentationProfile: true);

    $medications = $patient->medications()->orderBy('name')->get();

    expect($medications)->toHaveCount(2);
    expect($medications->pluck('name')->all())->toBe(['Magnesiumcitraat', 'Metformine']);

    $metformin = $medications->firstWhere('name', 'Metformine');
    expect($metformin?->schedules()->first()?->dose_time)->toBe('15:30');
    expect($metformin?->schedules()->first()?->times_per_day)->toBe('1');

    $magnesium = $medications->firstWhere('name', 'Magnesiumcitraat');
    expect($magnesium?->schedules()->first()?->dose_time)->toBe('21:00');
    expect($magnesium?->schedules()->first()?->times_per_day)->toBe('1');
});

test('presentation seeder replaces demo patient medications with two fixed intake times', function () {
    $familyUser = User::factory()->familyMember()->create([
        'email' => DatabaseSeeder::DEMO_FAMILY_EMAIL,
    ]);
    $patientUser = User::factory()->patient()->create([
        'email' => DatabaseSeeder::DEMO_PATIENT_EMAIL,
    ]);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $family = $familyUser->familyOrCreate();
    $patient->families()->syncWithoutDetaching([$family->id]);

    (new MedicationSeeder)->run($patient, $family);
    expect($patient->medications()->count())->toBe(3);

    (new PresentationSeeder)->run();

    $patient->refresh();
    $medications = $patient->medications()->orderBy('name')->get();

    expect($medications)->toHaveCount(2);
    expect($medications->firstWhere('name', 'Metformine')?->schedules()->first()?->dose_time)->toBe('15:30');
    expect($medications->firstWhere('name', 'Magnesiumcitraat')?->schedules()->first()?->dose_time)->toBe('21:00');
});
