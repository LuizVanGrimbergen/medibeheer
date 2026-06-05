<?php

use App\Enums\DailyMoodScore;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationType;
use App\Models\DailyCheckin;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\User;
use App\Support\Medications\MedicationIntakeClock;
use Carbon\CarbonImmutable;

test('family overview includes today check-ins for the active patient', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => now()->toDateString(),
        'mood_score' => DailyMoodScore::BAD->value,
        'note' => 'Kortademig.',
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Overview')
            ->has('updates_checkins', 1)
            ->where('updates_checkins.0.mood_score', DailyMoodScore::BAD->value)
            ->where('updates_checkins.0.patient_name', $patientUser->name));
});

test('family overview omits check-ins from other days on updates props', function () {
    $this->travelTo('2026-05-19');

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => '2026-05-19',
        'mood_score' => DailyMoodScore::GOOD->value,
        'note' => null,
    ]);

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => '2026-05-10',
        'mood_score' => DailyMoodScore::BAD->value,
        'note' => null,
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('updates_checkins', 1)
            ->where('updates_checkins.0.checkin_date', '2026-05-19'));
});

test('family overview includes today taken medication intakes for the active patient', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 10:00:00', MedicationIntakeClock::TIMEZONE),
    );

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
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

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('updates_medication_intakes', 1)
            ->where('updates_medication_intakes.0.name', 'Paracetamol')
            ->where('updates_medication_intakes.0.intake_date', '2026-05-19'));

    CarbonImmutable::setTestNow();
});

test('family overview exposes empty updates when no patient is linked', function () {
    $familyUser = User::factory()->familyMember()->create();

    $this->actingAs($familyUser)->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('updates_checkins', [])
            ->where('updates_medication_intakes', []));
});
