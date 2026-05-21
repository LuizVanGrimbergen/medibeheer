<?php

use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationType;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Models\User;
use App\Notifications\Patient\MedicationIntakeDueNotification;
use App\Notifications\Patient\MedicationIntakeMissedNotification;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\PatientMedicationReminderTranslations;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

function seedPatientMedicationDueReminderScenario(): array
{
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:00:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $user->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-test-subscription',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Paracetamol',
        'type_medication' => MedicationType::PILL,
    ]);

    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '09:00',
        'snooze_time' => '30',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    return [$user, $patient];
}

test('medication due reminder command notifies patients with due intakes', function () {
    Notification::fake();

    [$user] = seedPatientMedicationDueReminderScenario();

    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertSentTo($user, MedicationIntakeDueNotification::class, function (MedicationIntakeDueNotification $notification): bool {
        return $notification->slot['name'] === 'Paracetamol'
            && $notification->slot['dose_time'] === '09:00';
    });

    CarbonImmutable::setTestNow();
});

test('medication due notification uses elderly friendly dutch copy', function () {
    Notification::fake();

    [$user] = seedPatientMedicationDueReminderScenario();

    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertSentTo($user, MedicationIntakeDueNotification::class, function (
        MedicationIntakeDueNotification $notification,
    ) use ($user): bool {
        $message = $notification->toWebPush($user, $notification)->toArray();

        return $message['title'] === PatientMedicationReminderTranslations::trans(
            'patient_medication_reminders.notification.title',
        )
            && $message['body'] === PatientMedicationReminderTranslations::trans(
                'patient_medication_reminders.notification.body',
                [
                    'name' => 'Paracetamol',
                    'type' => 'Tablet / pil',
                ],
            );
    });

    CarbonImmutable::setTestNow();
});

test('medication due reminder command sends only one notification per intake per day', function () {
    Notification::fake();

    seedPatientMedicationDueReminderScenario();

    Artisan::call('patient:send-medication-due-reminders');
    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertCount(1);

    CarbonImmutable::setTestNow();
});

test('medication due reminder command skips patients without push subscriptions', function () {
    Notification::fake();

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:00:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '09:00',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertNothingSent();

    CarbonImmutable::setTestNow();
});

test('medication due reminder is not sent after the dose time minute has passed', function () {
    Notification::fake();

    [$user] = seedPatientMedicationDueReminderScenario();

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:05:00', MedicationIntakeClock::TIMEZONE),
    );

    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertNothingSentTo($user);

    CarbonImmutable::setTestNow();
});

test('test push notification command sends immediately to subscribed patient', function () {
    Notification::fake();

    [$user] = seedPatientMedicationDueReminderScenario();

    Artisan::call('patient:send-test-push-notification', ['user' => $user->id]);

    Notification::assertSentTo($user, MedicationIntakeDueNotification::class);

    CarbonImmutable::setTestNow();
});

test('medication due reminder command skips intakes already taken', function () {
    Notification::fake();

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:00:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $user->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-test-subscription-taken',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

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
    ])->assertRedirect(route('patient.dashboard'));

    Cache::flush();

    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertNothingSent();

    CarbonImmutable::setTestNow();
});

test('medication missed reminder is sent at the exact snooze end minute when intake was not taken', function () {
    Notification::fake();

    [$user] = seedPatientMedicationDueReminderScenario();

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:30:00', MedicationIntakeClock::TIMEZONE),
    );

    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertSentTo($user, MedicationIntakeMissedNotification::class, function (
        MedicationIntakeMissedNotification $notification,
    ) use ($user): bool {
        $message = $notification->toWebPush($user, $notification)->toArray();

        return $message['title'] === PatientMedicationReminderTranslations::trans(
            'patient_medication_reminders.missed_notification.title',
        )
            && ($message['data']['openUrl'] ?? null) === route('patient.dashboard')
            && ($message['data']['markTakenUrl'] ?? null) === null;
    });

    Notification::assertNotSentTo($user, MedicationIntakeDueNotification::class);

    CarbonImmutable::setTestNow();
});

test('medication missed reminder is not sent when intake was already taken', function () {
    Notification::fake();

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:30:00', MedicationIntakeClock::TIMEZONE),
    );

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $user->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-test-subscription-missed-taken',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    $medication = Medication::factory()->for($patient)->create([
        'type_medication' => MedicationType::PILL,
    ]);

    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'dose_time' => '09:00',
        'snooze_time' => '30',
        'start_date' => '2026-05-01',
        'end_date' => null,
    ]);

    $this->actingAs($user)->post(route('patient.medication-intakes.store'), [
        'medication_schedule_id' => $schedule->id,
        'dose_time' => '09:00',
    ])->assertRedirect(route('patient.dashboard'));

    Cache::flush();

    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertNothingSent();

    CarbonImmutable::setTestNow();
});

test('medication missed reminder is sent only once per intake per day', function () {
    Notification::fake();

    [$user] = seedPatientMedicationDueReminderScenario();

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:30:00', MedicationIntakeClock::TIMEZONE),
    );

    Artisan::call('patient:send-medication-due-reminders');
    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertSentToTimes($user, MedicationIntakeMissedNotification::class, 1);

    CarbonImmutable::setTestNow();
});

test('due and missed reminders can both be sent on the same day', function () {
    Notification::fake();

    [$user] = seedPatientMedicationDueReminderScenario();

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:00:00', MedicationIntakeClock::TIMEZONE),
    );

    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertSentToTimes($user, MedicationIntakeDueNotification::class, 1);
    Notification::assertNotSentTo($user, MedicationIntakeMissedNotification::class);

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-19 09:30:00', MedicationIntakeClock::TIMEZONE),
    );

    Artisan::call('patient:send-medication-due-reminders');

    Notification::assertSentToTimes($user, MedicationIntakeDueNotification::class, 1);
    Notification::assertSentToTimes($user, MedicationIntakeMissedNotification::class, 1);

    CarbonImmutable::setTestNow();
});
