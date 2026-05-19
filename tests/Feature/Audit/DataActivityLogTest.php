<?php

use App\Enums\AppointmentStatus;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationType;
use App\Models\Appointment;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\User;
use App\Services\Audit\ActivityLogName;
use App\Support\Medications\MedicationIntakeClock;
use Carbon\CarbonImmutable;
use Spatie\Activitylog\Models\Activity;

test('updating a medication records a data activity log', function () {
    $user = User::factory()->patient()->create();
    $medication = Medication::factory()->for($user->patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $this->actingAs($user);

    $medication->update([
        'type_medication' => MedicationType::LIQUID,
    ]);

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::DATA)
        ->where('subject_type', $medication->getMorphClass())
        ->where('subject_id', $medication->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id)
        ->and($activity->properties->get('patient_id'))->toBe($user->patient->id)
        ->and($activity->properties->get('attributes'))->toHaveKey('type_medication');
});

test('soft deleting a medication records deleted_at in the data activity log', function () {
    $user = User::factory()->patient()->create();
    $medication = Medication::factory()->for($user->patient)->create();

    $this->actingAs($user);

    $medication->delete();

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::DATA)
        ->where('subject_id', $medication->id)
        ->where('event', 'deleted')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id);
});

test('marking a medication intake as taken records a data activity log', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-15 09:20:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $medication = Medication::factory()->for($user->patient)->create([
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
    ]);

    $intake = MedicationIntake::firstOrNewForScheduleDateAndDoseTime(
        $schedule->id,
        '2026-05-15',
        '09:00',
    );

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::DATA)
        ->where('subject_type', $intake->getMorphClass())
        ->where('subject_id', $intake->id)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id)
        ->and($activity->properties->get('patient_id'))->toBe($user->patient->id);

    CarbonImmutable::setTestNow();
});

test('updating an appointment status records a data activity log', function () {
    $user = User::factory()->patient()->create();
    $appointment = Appointment::factory()->for($user->patient)->create([
        'status' => AppointmentStatus::SCHEDULED,
    ]);

    $this->actingAs($user);

    $appointment->update([
        'status' => AppointmentStatus::CANCELLED,
    ]);

    $activity = Activity::query()
        ->where('log_name', ActivityLogName::DATA)
        ->where('subject_type', $appointment->getMorphClass())
        ->where('subject_id', $appointment->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($user->id)
        ->and($activity->properties->get('attributes'))->toHaveKey('status');

});
