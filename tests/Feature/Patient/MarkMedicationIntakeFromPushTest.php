<?php

use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationType;
use App\Events\Family\MedicationIntakeRecordedEvent;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\User;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationIntakePushMarkUrl;
use App\Support\Medications\PatientRecentPushMedicationMarkStore;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

test('signed push mark link records medication intake for today', function () {
    Event::fake([MedicationIntakeRecordedEvent::class]);

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:15:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '09:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    $url = MedicationIntakePushMarkUrl::forSlot([
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '09:00',
    ]);

    $this->post($url, [], ['X-Push-Mark' => '1'])->assertNoContent();

    $recentMarkStore = app(PatientRecentPushMedicationMarkStore::class);
    expect($recentMarkStore->peek($patient->id))->toBe($medication->name);

    $intake = MedicationIntake::query()
        ->where('medication_schedule_id', $schedule->id)
        ->whereDate('intake_date', '2026-05-19')
        ->first();

    expect($intake)->not->toBeNull()
        ->and($intake->taken_at)->not->toBeNull();

    Event::assertDispatched(MedicationIntakeRecordedEvent::class);

    CarbonImmutable::setTestNow();
});

test('signed push mark get redirects to push success page when authenticated', function () {
    Event::fake([MedicationIntakeRecordedEvent::class]);

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:15:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '09:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    $url = MedicationIntakePushMarkUrl::forSlot([
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '09:00',
    ]);

    $this->actingAs($user)
        ->get($url)
        ->assertRedirect(route('patient.medication-push-mark.success'));

    $this->actingAs($user)
        ->get(route('patient.medication-push-mark.success'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Patient/MedicationPushMarkSuccess')
            ->where('medication_name', 'Paracetamol'));

    CarbonImmutable::setTestNow();
});

test('signed push mark get redirects to success page when opened in browser', function () {
    Event::fake([MedicationIntakeRecordedEvent::class]);

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:15:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $schedule = MedicationSchedule::factory()
        ->forMedication(Medication::factory()->for($patient)->create())
        ->create([
            'intake_frequency' => MedicationIntakeFrequency::DAILY,
            'dose_time' => '09:00',
            'start_date' => '2026-05-01',
            'end_date' => null,
        ]);

    $url = URL::temporarySignedRoute(
        'patient.medication-intakes.mark-from-push',
        MedicationIntakeClock::today()->endOfDay(),
        [
            'medicationSchedule' => $schedule->id,
            'doseTime' => '09:00',
        ],
    );

    $this->actingAs($user)
        ->get($url)
        ->assertRedirect(route('patient.medication-push-mark.success'));

    CarbonImmutable::setTestNow();
});

test('invalid signature on push mark link is rejected', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:15:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $schedule = MedicationSchedule::factory()
        ->forMedication(Medication::factory()->for($patient)->create())
        ->create([
            'intake_frequency' => MedicationIntakeFrequency::DAILY,
            'dose_time' => '09:00',
            'start_date' => '2026-05-01',
            'end_date' => null,
        ]);

    $url = URL::temporarySignedRoute(
        'patient.medication-intakes.mark-from-push',
        MedicationIntakeClock::today()->endOfDay(),
        [
            'medicationSchedule' => $schedule->id,
            'doseTime' => '09:00',
        ],
    ).'&signature=invalid';

    $this->get($url)->assertForbidden();

    CarbonImmutable::setTestNow();
});
