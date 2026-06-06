<?php

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationType;
use App\Events\Family\MedicationIntakeRecordedEvent;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\User;
use App\Support\Medications\MedicationIntakeClock;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

function setMedicationIntakeTestNow(string $localDateTime): void
{
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse($localDateTime, MedicationIntakeClock::TIMEZONE),
    );
}

test('patient dashboard includes today medication intake slots', function () {
    setMedicationIntakeTestNow('2026-05-15 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE,
        'type_medication' => MedicationType::PILL,
        'strength' => null,
        'note' => 'Met water innemen.',
    ]);

    MedicationSchedule::factory()->forMedication($medication)->create([
        'meal_timing' => MedicationMealTiming::UNRELATED,
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '2',
        'dose_quantity' => '1',
        'dose_time' => '08:00, 20:00',
        'snooze_time' => '30, 30',
        'start_date' => '2026-05-01',
        'end_date' => '2026-12-31',
    ]);

    $response = $this->actingAs($user)->get(route('patient.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Patient/Dashboard')
        ->loadDeferredProps(fn ($page) => $page
            ->has('today_medication_intakes', 2)
            ->where('today_medication_intakes.0.name', 'Paracetamol')
            ->where('today_medication_intakes.0.dose_time', '08:00')
            ->where('today_medication_intakes.0.snooze_minutes', 30)
            ->where('today_medication_intakes.0.intake_window_state', 'past')
            ->where('today_medication_intakes.0.day_period', 'morning')
            ->where('today_medication_intakes.0.meal_timing', MedicationMealTiming::UNRELATED->value)
            ->where('today_medication_intakes.0.intake_frequency', MedicationIntakeFrequency::DAILY)
            ->where('today_medication_intakes.0.intake_weekdays', null)
            ->where('today_medication_intakes.0.note', 'Met water innemen.')
            ->where('today_medication_intakes.0.strength', null)
            ->where('today_medication_intakes.0.schedule_start_date', '2026-05-01')
            ->where('today_medication_intakes.0.schedule_end_date', '2026-12-31')
            ->where('today_medication_intakes.0.schedule_dose_times', ['08:00', '20:00'])
            ->where('today_medication_intakes.0.taken_at', null)
            ->where('today_medication_intakes.1.dose_time', '20:00')
            ->where('today_medication_intakes.1.day_period', 'evening')));

    CarbonImmutable::setTestNow();
});

test('recording a medication intake dispatches a family updates broadcast event', function () {
    Event::fake([MedicationIntakeRecordedEvent::class]);

    setMedicationIntakeTestNow('2026-05-15 09:20:00');

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

    $this->actingAs($user)->post(route('patient.medication-intakes.store'), [
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '09:00',
    ])->assertRedirect(route('patient.medication-push-mark.success'));

    Event::assertDispatched(MedicationIntakeRecordedEvent::class, function (MedicationIntakeRecordedEvent $event) use ($patient): bool {
        return $event->intake->patient_id === $patient->id
            && $event->intake->intake_date->toDateString() === '2026-05-15';
    });

    CarbonImmutable::setTestNow();
});

test('patients can mark a due medication intake as taken', function () {
    setMedicationIntakeTestNow('2026-05-15 09:20:00');

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

    $response = $this->actingAs($user)->post(route('patient.medication-intakes.store'), [
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '09:00',
    ]);

    $response->assertRedirect(route('patient.medication-push-mark.success'));

    $intake = MedicationIntake::firstOrNewForScheduleDateAndDoseTime(
        $schedule->id,
        '2026-05-15',
        '09:00',
    );

    expect($intake->exists)->toBeTrue()
        ->and($intake->dose_time)->toBe('09:00');

    CarbonImmutable::setTestNow();
});

test('medication intake dose time is encrypted at rest', function () {
    setMedicationIntakeTestNow('2026-05-15 09:20:00');

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

    $this->actingAs($user)->post(route('patient.medication-intakes.store'), [
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '09:00',
    ])->assertRedirect(route('patient.medication-push-mark.success'));

    $intake = MedicationIntake::firstOrNewForScheduleDateAndDoseTime(
        $schedule->id,
        '2026-05-15',
        '09:00',
    );

    expect($intake->dose_time)->toBe('09:00');

    $raw = DB::table('medication_intakes')->where('id', $intake->id)->first();
    expect($raw->dose_time)->not->toBe('09:00');

    CarbonImmutable::setTestNow();
});

test('marking the same intake again updates the existing record', function () {
    setMedicationIntakeTestNow('2026-05-15 09:20:00');

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

    MedicationIntake::factory()->forSchedule($schedule)->create([
        'intake_date' => '2026-05-15',
        'dose_time' => '09:00',
        'taken_at' => now()->subHour(),
    ]);

    $this->actingAs($user)->post(route('patient.medication-intakes.store'), [
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '09:00',
    ])->assertRedirect(route('patient.medication-push-mark.success'));

    expect(MedicationIntake::query()->count())->toBe(1);

    CarbonImmutable::setTestNow();
});

test('patients can mark an intake as taken after the snooze window with late intake', function () {
    setMedicationIntakeTestNow('2026-05-15 18:45:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '18:30',
        'snooze_time' => '30',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    $this->actingAs($user)->post(route('patient.medication-intakes.store'), [
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '18:30',
        'late_intake' => true,
    ])->assertRedirect(route('patient.medication-push-mark.success'));

    expect(MedicationIntake::query()->where('patient_id', $patient->id)->exists())->toBeTrue();

    CarbonImmutable::setTestNow();
});

test('patients can register a custom taken time after the snooze window', function () {
    setMedicationIntakeTestNow('2026-05-15 19:30:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '18:30',
        'snooze_time' => '30',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    $this->actingAs($user)->post(route('patient.medication-intakes.store'), [
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '18:30',
        'taken_at' => '2026-05-15 18:50:00',
    ])->assertRedirect(route('patient.medication-push-mark.success'));

    $intake = MedicationIntake::query()
        ->where('patient_id', $patient->id)
        ->first();

    expect($intake)->not->toBeNull();
    expect($intake->taken_at?->timezone(MedicationIntakeClock::TIMEZONE)->format('Y-m-d H:i'))
        ->toBe('2026-05-15 18:50');

    CarbonImmutable::setTestNow();
});

test('intake outside the snooze window is rejected without late intake or custom time', function () {
    setMedicationIntakeTestNow('2026-05-15 19:30:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '18:30',
        'snooze_time' => '30',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    $this->actingAs($user)
        ->from(route('patient.dashboard'))
        ->post(route('patient.medication-intakes.store'), [
            'medication_schedule_id' => $schedule->id,
            'dose_time' => '18:30',
        ])
        ->assertRedirect(route('patient.dashboard'))
        ->assertSessionHasErrors('dose_time');

    expect(MedicationIntake::query()->where('patient_id', $patient->id)->exists())->toBeFalse();

    CarbonImmutable::setTestNow();
});

test('weekday-only medications are omitted on non-scheduled days', function () {
    setMedicationIntakeTestNow('2026-05-17 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::WEEKDAYS,
        'dose_time' => '08:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);
    $schedule->syncIntakeWeekdays([1, 2, 3, 4, 5]);

    $this->actingAs($user)
        ->get(route('patient.dashboard'))
        ->assertInertia(fn ($page) => $page->loadDeferredProps(
            fn ($page) => $page->has('today_medication_intakes', 0),
        ));

    CarbonImmutable::setTestNow();
});
