<?php

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PatientWebPushDemoSeeder;

test('patient web push demo seeder removes legacy demo push reminder medications', function () {
    $user = User::factory()->patient()->create([
        'email' => DatabaseSeeder::DEMO_PATIENT_EMAIL,
    ]);
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $patient->medications()->create([
        'name' => PatientWebPushDemoSeeder::DEMO_PUSH_REMINDER_MEDICATION_NAME,
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE,
        'type_medication' => MedicationType::PILL,
        'stock_pieces_per_package' => 30,
    ]);
    $patient->medications()->create([
        'name' => PatientWebPushDemoSeeder::DEMO_PUSH_REMINDER_MEDICATION_NAME,
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE,
        'type_medication' => MedicationType::PILL,
        'stock_pieces_per_package' => 30,
    ]);

    expect($patient->medications()->count())->toBe(2);

    (new PatientWebPushDemoSeeder)->run($user);

    expect($patient->medications()->count())->toBe(0);
});
