<?php

use App\Enums\DailyMoodScore;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationType;
use App\Models\DailyCheckin;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\Patient;
use App\Models\User;
use App\Support\Medications\MedicationIntakeClock;
use Carbon\CarbonImmutable;

test('linked family members see daily check-ins on updates', function () {
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

    $this->actingAs($familyUser)->get(route('family.updates'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Updates')
            ->where('updates_period_days', 3)
            ->has('updates_checkins', 1)
            ->where('updates_checkins.0.mood_score', DailyMoodScore::BAD->value));
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
        'checkin_date' => '2026-05-10',
        'mood_score' => DailyMoodScore::BAD->value,
        'note' => null,
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.updates', ['period_days' => 1]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('updates_checkins', 1)
            ->where('updates_checkins.0.checkin_date', '2026-05-19'));
});

test('linked family members may subscribe to patient family updates channel', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)
        ->postJson('/broadcasting/auth', [
            'channel_name' => 'private-patient.'.$patient->id.'.family-updates',
        ])
        ->assertOk();
});

test('linked family members see taken medication intakes on updates', function () {
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

    $this->actingAs($familyUser)->get(route('family.updates'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Updates')
            ->has('updates_medication_intakes', 1)
            ->where('updates_medication_intakes.0.name', 'Paracetamol')
            ->where('updates_medication_intakes.0.intake_date', '2026-05-19'));

    CarbonImmutable::setTestNow();
});

test('family updates only includes medication intakes within the selected period', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 10:00:00', MedicationIntakeClock::TIMEZONE),
    );

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
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
        'taken_at' => CarbonImmutable::parse('2026-05-19 09:00:00', MedicationIntakeClock::TIMEZONE),
    ])->save();

    MedicationIntake::firstOrNewForScheduleDateAndDoseTime(
        $schedule->id,
        '2026-05-10',
        '09:00',
    )->fill([
        'patient_id' => $patient->id,
        'medication_id' => $medication->id,
        'taken_at' => CarbonImmutable::parse('2026-05-10 09:00:00', MedicationIntakeClock::TIMEZONE),
    ])->save();

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.updates', ['period_days' => 1]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('updates_medication_intakes', 1)
            ->where('updates_medication_intakes.0.intake_date', '2026-05-19'));

    CarbonImmutable::setTestNow();
});

test('only users who may view the patient may use the family updates channel', function () {
    $patient = User::factory()->patient()->create()->patient;
    expect($patient)->toBeInstanceOf(Patient::class);

    $familyUser = createLinkedFamilyMemberForPatient($patient);
    $doctorUser = User::factory()->doctor()->create();

    expect($familyUser->can('view', $patient))->toBeTrue();
    expect($doctorUser->can('view', $patient))->toBeFalse();
});
