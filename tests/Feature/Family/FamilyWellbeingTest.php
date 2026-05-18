<?php

use App\Enums\DailyMoodScore;
use App\Models\DailyCheckin;
use App\Models\User;
use Carbon\CarbonImmutable;

test('linked family members see wellbeing check-ins on wellbeing', function () {
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

    $response = $this->actingAs($familyUser)->get(route('family.wellbeing'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Family/Wellbeing')
        ->has('wellbeing_checkins.data', 1)
        ->where('wellbeing_checkins.data.0.mood_score', DailyMoodScore::OK->value)
        ->where('wellbeing_checkins.data.0.note', 'Even uitgerust.'));
});

test('family members without a patient link see empty wellbeing data on wellbeing', function () {
    $familyUser = User::factory()->familyMember()->create();

    $response = $this->actingAs($familyUser)->get(route('family.wellbeing'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Family/Wellbeing')
        ->has('wellbeing_checkins.data', 0));
});

test('family updates page does not load wellbeing props', function () {
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
            ->missing('wellbeing_checkins'));
});

test('linked family members can visit family wellbeing', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $response = $this->actingAs($familyUser)->get(route('family.wellbeing'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Family/Wellbeing'));
});

test('shared family props expose active patient today mood for footer nav', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => now()->toDateString(),
        'mood_score' => DailyMoodScore::GOOD->value,
        'note' => null,
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('family.active_patient_today_mood', DailyMoodScore::GOOD->value));
});

test('shared family props expose bad mood when check-in is bad', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => now()->toDateString(),
        'mood_score' => DailyMoodScore::BAD->value,
        'note' => null,
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('family.active_patient_today_mood', DailyMoodScore::BAD->value));
});

test('family wellbeing calendar only loads check-ins for the requested month', function () {
    $this->travelTo(CarbonImmutable::parse('2026-05-15'));

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => '2026-05-10',
        'mood_score' => DailyMoodScore::BAD->value,
        'note' => null,
    ]);

    DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => '2026-04-05',
        'mood_score' => DailyMoodScore::GOOD->value,
        'note' => null,
    ]);

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.wellbeing', ['calendar_month' => '2026-05']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Family/Wellbeing')
            ->where('wellbeing_calendar_month', '2026-05')
            ->has('wellbeing_calendar_checkins', 1)
            ->where('wellbeing_calendar_checkins.0.checkin_date', '2026-05-10')
            ->where('wellbeing_calendar_checkins.0.mood_score', DailyMoodScore::BAD->value));
});

test('shared family props use null today mood when there is no check-in today', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('family.active_patient_today_mood', null));
});
