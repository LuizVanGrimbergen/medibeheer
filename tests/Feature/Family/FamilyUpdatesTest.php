<?php

use App\Enums\DailyMoodScore;
use App\Enums\MedicationIntakeFrequency;
use App\Models\DailyCheckin;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\User;
use Carbon\CarbonImmutable;

test('linked family members see daily check-ins on updates', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => now()->toDateString(),
        'mood_score' => DailyMoodScore::BAD->value,
        'note' => 'Na een korte trap merkbaar kortademig.',
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.updates'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Updates')
            ->where('updates_period_days', 3)
            ->has('updates_checkins', 1)
            ->where('updates_checkins.0.mood_score', DailyMoodScore::BAD->value)
            ->where('updates_checkins.0.note', 'Na een korte trap merkbaar kortademig.')
            ->missing('wellbeing_checkins')
            ->missing('wellbeing_calendar_checkins'));
});

test('family members without a patient link see empty updates data', function () {
    $familyUser = User::factory()->familyMember()->create();

    $this->actingAs($familyUser)->get(route('family.updates'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Updates')
            ->where('updates_period_days', 3)
            ->has('updates_checkins', 0));
});

test('family updates page does not load wellbeing calendar props', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => now()->toDateString(),
        'mood_score' => DailyMoodScore::OK->value,
        'note' => 'Even uitgerust.',
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.updates'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Updates')
            ->missing('wellbeing_checkins')
            ->missing('wellbeing_calendar_month'));
});

test('family updates only includes check-ins within the selected period', function () {
    $this->travelTo(CarbonImmutable::parse('2026-05-19'));

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
        'checkin_date' => '2026-05-17',
        'mood_score' => DailyMoodScore::OK->value,
        'note' => null,
    ]);

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => '2026-05-10',
        'mood_score' => DailyMoodScore::BAD->value,
        'note' => null,
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.updates', ['period_days' => 3]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('updates_period_days', 3)
            ->has('updates_checkins', 2)
            ->where('updates_checkins.0.checkin_date', '2026-05-19')
            ->where('updates_checkins.1.checkin_date', '2026-05-17'));

    $this->actingAs($familyUser)->get(route('family.updates', ['period_days' => 1]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('updates_period_days', 1)
            ->has('updates_checkins', 1)
            ->where('updates_checkins.0.checkin_date', '2026-05-19'));

    $this->actingAs($familyUser)->get(route('family.updates', ['period_days' => 5]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('updates_period_days', 5)
            ->has('updates_checkins', 2));
});

test('family updates includes medication intake slots within the selected period', function () {
    CarbonImmutable::setTestNow('2026-05-19 10:00:00');

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '09:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    MedicationIntake::factory()->forSchedule($schedule)->create([
        'intake_date' => '2026-05-19',
        'dose_time' => '09:00',
        'taken_at' => now(),
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.updates', ['period_days' => 1]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('updates_medication_slots', 1)
            ->where('updates_medication_slots.0.name', 'Paracetamol')
            ->where('updates_medication_slots.0.intake_date', '2026-05-19')
            ->where('updates_medication_slots.0.dose_time', '09:00'));

    CarbonImmutable::setTestNow();
});

test('family updates falls back to three days for invalid period', function () {
    $familyUser = User::factory()->familyMember()->create();

    $this->actingAs($familyUser)->get(route('family.updates', ['period_days' => 7]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('updates_period_days', 3));
});
