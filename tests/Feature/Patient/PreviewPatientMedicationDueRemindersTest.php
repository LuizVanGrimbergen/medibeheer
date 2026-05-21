<?php

use App\Support\Medications\MedicationIntakeClock;
use Carbon\CarbonImmutable;

test('preview command lists due slots from patient medication schedules', function () {
    [$user] = seedPatientMedicationDueReminderScenario();

    $this->artisan('patient:preview-medication-due-reminders', ['user' => $user->id])
        ->expectsOutputToContain('Paracetamol')
        ->assertSuccessful();
});

test('preview command reports no due slots when intake window has passed', function () {
    [$user] = seedPatientMedicationDueReminderScenario();

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 14:00:00', MedicationIntakeClock::TIMEZONE),
    );

    $this->artisan('patient:preview-medication-due-reminders', ['user' => $user->id])
        ->expectsOutputToContain('No intake at the exact dose_time minute right now')
        ->assertSuccessful();

    CarbonImmutable::setTestNow();
});
