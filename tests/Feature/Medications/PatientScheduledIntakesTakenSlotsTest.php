<?php

use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationType;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\User;
use App\Services\Medications\PatientScheduledIntakesQuery;
use App\Support\Medications\MedicationIntakeClock;
use Carbon\CarbonImmutable;

test('taken slots within days includes recorded intakes', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 10:00:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '1',
        'dose_time' => '09:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    MedicationIntake::firstOrNewForScheduleDateAndDoseTime(
        $schedule->id,
        '2026-05-19',
        '09:00',
    )->fill([
        'patient_id' => $patient->id,
        'medication_id' => $medication->id,
        'taken_at' => CarbonImmutable::parse('2026-05-19 09:15:00', MedicationIntakeClock::TIMEZONE),
    ])->save();

    $slots = app(PatientScheduledIntakesQuery::class)->takenSlotsWithinDaysForPatient($patient, 3);

    expect($slots)->toHaveCount(1)
        ->and($slots[0]['intake_date'])->toBe('2026-05-19');

    CarbonImmutable::setTestNow();
});
