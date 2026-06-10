<?php

use App\Enums\DailyCheckinSymptom;
use App\Enums\DailyMoodScore;
use App\Models\DailyCheckin;
use App\Models\User;
use App\Services\Patient\DailyCheckinEncouragementService;
use Illuminate\Support\Facades\Http;

test('good mood without note or symptoms uses static encouragement message', function () {
    config(['services.openai.key' => 'test-key']);

    Http::fake();

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $checkin = DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => now()->toDateString(),
        'mood_score' => DailyMoodScore::GOOD,
        'note' => null,
    ]);

    $message = app(DailyCheckinEncouragementService::class)->ensureMessage($checkin);

    expect($message)->toBe(trans('daily_checkin.encouragement.good'));
    Http::assertNothingSent();
    expect($checkin->fresh()->encouragement_message)->toBe($message);
});

test('bad mood check-in requests a personalized encouragement message from OpenAI', function () {
    config([
        'services.openai.key' => 'test-key',
        'services.openai.model' => 'gpt-4o-mini',
    ]);

    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Het is oké dat vandaag zwaarder voelt. Neem rust en wees lief voor uzelf.',
                    ],
                ],
            ],
        ]),
    ]);

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $checkin = DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => now()->toDateString(),
        'mood_score' => DailyMoodScore::BAD,
        'note' => 'Even moe vandaag.',
    ]);

    $checkin->selectedSymptoms()->create([
        'symptom' => DailyCheckinSymptom::FATIGUE->value,
    ]);

    $message = app(DailyCheckinEncouragementService::class)->ensureMessage($checkin->fresh());

    expect($message)->toBe('Het is oké dat vandaag zwaarder voelt. Neem rust en wees lief voor uzelf.');
    Http::assertSentCount(1);
    expect($checkin->fresh()->encouragement_message)->toBe($message);
});

test('encouragement generation falls back when OpenAI fails', function () {
    config(['services.openai.key' => 'test-key']);

    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([], 500),
    ]);

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $checkin = DailyCheckin::query()->create([
        'patient_id' => $patient->id,
        'checkin_date' => now()->toDateString(),
        'mood_score' => DailyMoodScore::OK,
        'note' => 'Niet zo goed.',
    ]);

    $message = app(DailyCheckinEncouragementService::class)->ensureMessage($checkin);

    expect($message)->toBe(trans('daily_checkin.encouragement.comfort'));
});

test('storing a daily check-in flashes encouragement for the success screen', function () {
    config(['services.openai.key' => 'test-key']);

    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Dank u voor uw check-in. We wensen u een rustige dag.',
                    ],
                ],
            ],
        ]),
    ]);

    $user = User::factory()->patient()->create();
    expect($user->patient)->not->toBeNull();

    $csrfToken = 'test-csrf-token';

    $this->actingAs($user)
        ->withSession(['_token' => $csrfToken])
        ->post(route('patient.daily-checkins.store'), [
            '_token' => $csrfToken,
            'mood_score' => DailyMoodScore::OK->value,
            'symptoms' => [DailyCheckinSymptom::FATIGUE->value],
            'note' => 'Even moe.',
        ])
        ->assertRedirect(route('patient.dashboard'))
        ->assertSessionHas('daily_checkin_mood', DailyMoodScore::OK->value)
        ->assertSessionHas(
            'daily_checkin_encouragement',
            'Dank u voor uw check-in. We wensen u een rustige dag.',
        );
});

test('patient dashboard exposes flashed encouragement on the success screen', function () {
    $user = User::factory()->patient()->create();

    $this->actingAs($user)
        ->withSession([
            'daily_checkin_mood' => DailyMoodScore::OK->value,
            'daily_checkin_encouragement' => 'Dank u voor uw check-in. We wensen u een rustige dag.',
        ])
        ->get(route('patient.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Patient/Dashboard/Index')
            ->where('flash.daily_checkin_mood', DailyMoodScore::OK->value)
            ->where(
                'flash.daily_checkin_encouragement',
                'Dank u voor uw check-in. We wensen u een rustige dag.',
            ));
});
