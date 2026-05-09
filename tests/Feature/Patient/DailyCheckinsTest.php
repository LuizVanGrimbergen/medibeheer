<?php

// (ai generated)

use App\Enums\DailyCheckinSymptom;
use App\Enums\DailyMoodScore;
use App\Models\DailyCheckin;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('successful daily check-in flashes mood for confirmation screen', function () {
    $user = User::factory()->patient()->create();
    expect($user->patient)->not->toBeNull();

    $csrfToken = 'test-csrf-token';

    $this->actingAs($user)
        ->withSession(['_token' => $csrfToken])
        ->post(route('patient.daily-checkins.store'), [
            '_token' => $csrfToken,
            'mood_score' => DailyMoodScore::BAD->value,
            'symptoms' => [],
            'note' => null,
        ])
        ->assertRedirect(route('patient.dashboard'))
        ->assertSessionHas('daily_checkin_mood', DailyMoodScore::BAD->value);
});

test('patients can submit a daily check-in and re-submit on the same day', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $csrfToken = 'test-csrf-token';

    $this->actingAs($user)
        ->withSession(['_token' => $csrfToken])
        ->post(route('patient.daily-checkins.store'), [
            '_token' => $csrfToken,
            'mood_score' => DailyMoodScore::GOOD->value,
            'note' => 'Ging best oké vandaag.',
        ])
        ->assertRedirect(route('patient.dashboard'));

    $this->assertDatabaseHas('daily_checkins', [
        'patient_id' => $patient->id,
    ]);

    $first = DailyCheckin::query()->where('patient_id', $patient->id)->first();
    expect($first)->not->toBeNull();
    expect($first->mood_score)->toBe(DailyMoodScore::GOOD);
    expect($first->note)->toBe('Ging best oké vandaag.');

    $this->actingAs($user)
        ->withSession(['_token' => $csrfToken])
        ->post(route('patient.daily-checkins.store'), [
            '_token' => $csrfToken,
            'mood_score' => DailyMoodScore::BAD->value,
            'note' => null,
        ])
        ->assertRedirect(route('patient.dashboard'));

    $this->assertDatabaseCount('daily_checkins', 1);

    $fresh = $first->fresh();
    expect($fresh)->not->toBeNull();
    expect($fresh->mood_score)->toBe(DailyMoodScore::GOOD);
    expect($fresh->note)->toBe('Ging best oké vandaag.');
});

test('patients can submit a daily check-in with optional symptoms for bad or ok', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $csrfToken = 'test-csrf-token';

    $this->actingAs($user)
        ->withSession(['_token' => $csrfToken])
        ->post(route('patient.daily-checkins.store'), [
            '_token' => $csrfToken,
            'mood_score' => DailyMoodScore::OK->value,
            'symptoms' => [
                DailyCheckinSymptom::FATIGUE->value,
                DailyCheckinSymptom::POOR_SLEEP->value,
            ],
            'note' => null,
        ])
        ->assertRedirect(route('patient.dashboard'));

    $row = DailyCheckin::query()->where('patient_id', $patient->id)->first();
    expect($row)->not->toBeNull();
    expect($row->mood_score)->toBe(DailyMoodScore::OK);
    expect($row->symptomValues())->toBe([
        DailyCheckinSymptom::FATIGUE->value,
        DailyCheckinSymptom::POOR_SLEEP->value,
    ]);

    $this->assertDatabaseCount('daily_checkin_symptoms', 2);
});

test('patients cannot send symptoms when mood is good', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $csrfToken = 'test-csrf-token';

    $this->actingAs($user)
        ->withSession(['_token' => $csrfToken])
        ->post(route('patient.daily-checkins.store'), [
            '_token' => $csrfToken,
            'mood_score' => DailyMoodScore::GOOD->value,
            'symptoms' => [DailyCheckinSymptom::PAIN->value],
            'note' => null,
        ])
        ->assertSessionHasErrors('symptoms');
});

test('linked family members can view daily check-ins when they can view the patient', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = User::factory()->familyMember()->create();
    $family = $familyUser->familyOrCreate();
    $family->patients()->attach($patient->id);

    expect($familyUser->isFamilyLinkedToPatient($patient))->toBeTrue();

    $checkin = DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => now()->toDateString(),
        'mood_score' => DailyMoodScore::GOOD->value,
        'note' => null,
    ]);

    expect(Gate::forUser($patientUser)->allows('view', $checkin))->toBeTrue();
    expect(Gate::forUser($familyUser)->allows('view', $checkin))->toBeTrue();
});
