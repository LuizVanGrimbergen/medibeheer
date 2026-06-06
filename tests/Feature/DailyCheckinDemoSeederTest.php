<?php

use App\Enums\DailyMoodScore;
use App\Models\DailyCheckin;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DailyCheckinDemoSeeder;
use Database\Seeders\DatabaseSeeder;

test('daily checkin demo seeder leaves today empty for the demo patient', function () {
    Carbon::setTestNow('2026-06-06 12:00:00');

    $user = User::factory()->patient()->create([
        'email' => DatabaseSeeder::DEMO_PATIENT_EMAIL,
    ]);
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => '2026-06-06',
        'mood_score' => DailyMoodScore::GOOD,
        'note' => 'Bestaande check-in van vandaag.',
    ]);

    (new DailyCheckinDemoSeeder)->run($patient);

    expect($patient->dailyCheckins()->whereDate('checkin_date', '2026-06-06')->exists())->toBeFalse();
    expect($patient->dailyCheckins()->whereDate('checkin_date', '2026-06-05')->exists())->toBeTrue();
});
