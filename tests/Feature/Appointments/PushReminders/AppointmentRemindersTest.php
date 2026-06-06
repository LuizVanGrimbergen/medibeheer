<?php

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\Appointments\PushReminders\AppointmentReminderNotification;
use App\Support\Appointments\AppointmentClock;
use App\Support\Appointments\PushReminders\AppointmentReminderKind;
use App\Support\PushReminders\PushReminderAudience;
use App\Support\PushReminders\PushReminderRecipient;
use App\Support\PushReminders\ReminderTranslations;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

function seedAppointmentReminderScenario(
    string $startsAt,
    string $now = '2026-05-14 09:00:00',
    string $patientName = 'Sophie Maas',
): array {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse($now, AppointmentClock::TIMEZONE),
    );

    $patientUser = User::factory()->patient()->create(['name' => $patientName]);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $patientUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-appointment-patient',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    $appointment = Appointment::factory()->for($patient)->create([
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER,
        'provider_name' => 'City Clinic',
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => $startsAt,
    ]);

    return [$patientUser, $patient, $appointment];
}

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('two day appointment reminder command notifies patient for appointment in two calendar days', function () {
    Notification::fake();

    [$patientUser, , $appointment] = seedAppointmentReminderScenario('2026-05-16 14:30:00');

    Artisan::call('appointment:send-two-day-reminders');

    Notification::assertSentTo($patientUser, AppointmentReminderNotification::class, function (
        AppointmentReminderNotification $notification,
    ) use ($appointment): bool {
        return $notification->kind === AppointmentReminderKind::TwoDaysBefore
            && $notification->appointment['appointment_id'] === $appointment->id
            && $notification->appointment['provider_name'] === 'City Clinic'
            && $notification->recipient->openUrl === route('patient.appointments', [
                'appointment' => $appointment->id,
            ], absolute: false);
    });
});

test('two day appointment reminder command notifies linked family member', function () {
    Notification::fake();

    [$patientUser, $patient, $appointment] = seedAppointmentReminderScenario('2026-05-16 14:30:00');
    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $familyUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-appointment-family',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    Artisan::call('appointment:send-two-day-reminders');

    Notification::assertSentTo($familyUser, AppointmentReminderNotification::class, function (
        AppointmentReminderNotification $notification,
    ) use ($appointment): bool {
        return $notification->kind === AppointmentReminderKind::TwoDaysBefore
            && $notification->appointment['appointment_id'] === $appointment->id
            && $notification->recipient->openUrl === route('family.appointments', [
                'view' => 'planned',
                'appointment' => $appointment->id,
            ], absolute: false);
    });

    Notification::assertSentTo($patientUser, AppointmentReminderNotification::class);
});

test('two day appointment reminder command skips cancelled appointments', function () {
    Notification::fake();

    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-14 09:00:00', AppointmentClock::TIMEZONE),
    );

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $patientUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-appointment-cancelled',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::CANCELLED,
        'starts_at' => '2026-05-16 10:00:00',
    ]);

    Artisan::call('appointment:send-two-day-reminders');

    Notification::assertNothingSent();
});

test('two day appointment reminder command does not notify for appointments outside the window', function () {
    Notification::fake();

    seedAppointmentReminderScenario('2026-05-17 14:30:00');

    Artisan::call('appointment:send-two-day-reminders');

    Notification::assertNothingSent();
});

test('two day appointment reminder command sends only once per recipient and appointment', function () {
    Notification::fake();

    [$patientUser, , $appointment] = seedAppointmentReminderScenario('2026-05-16 14:30:00');

    Artisan::call('appointment:send-two-day-reminders');
    Artisan::call('appointment:send-two-day-reminders');

    Notification::assertSentToTimes($patientUser, AppointmentReminderNotification::class, 1);

    Cache::forget(sprintf(
        'appointment-reminder:%s:%d:%d',
        AppointmentReminderKind::TwoDaysBefore->value,
        $patientUser->id,
        $appointment->id,
    ));
});

test('two hour appointment reminder command notifies patient two hours before starts at', function () {
    Notification::fake();

    [$patientUser, , $appointment] = seedAppointmentReminderScenario(
        '2026-05-16 14:30:00',
        '2026-05-16 12:30:00',
    );

    Artisan::call('appointment:send-two-hour-reminders');

    Notification::assertSentTo($patientUser, AppointmentReminderNotification::class, function (
        AppointmentReminderNotification $notification,
    ) use ($appointment): bool {
        return $notification->kind === AppointmentReminderKind::TwoHoursBefore
            && $notification->appointment['appointment_id'] === $appointment->id
            && $notification->appointment['starts_at_time'] === '14:30';
    });
});

test('two hour appointment reminder command notifies linked family member', function () {
    Notification::fake();

    [$patientUser, $patient, $appointment] = seedAppointmentReminderScenario(
        '2026-05-16 14:30:00',
        '2026-05-16 12:30:00',
    );
    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $familyUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-appointment-family-two-hour',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    Artisan::call('appointment:send-two-hour-reminders');

    Notification::assertSentTo($familyUser, AppointmentReminderNotification::class, function (
        AppointmentReminderNotification $notification,
    ) use ($appointment): bool {
        return $notification->kind === AppointmentReminderKind::TwoHoursBefore
            && $notification->appointment['appointment_id'] === $appointment->id;
    });

    Notification::assertSentTo($patientUser, AppointmentReminderNotification::class);
});

test('two hour appointment reminder command does not notify outside the two hour window', function () {
    Notification::fake();

    seedAppointmentReminderScenario(
        '2026-05-16 14:30:00',
        '2026-05-16 12:29:00',
    );

    Artisan::call('appointment:send-two-hour-reminders');

    Notification::assertNothingSent();
});

test('appointment reminder notification uses translated copy for patient two days before', function () {
    $notification = new AppointmentReminderNotification(
        AppointmentReminderKind::TwoDaysBefore,
        [
            'appointment_id' => 1,
            'provider_name' => 'City Clinic',
            'doctor_type' => DoctorType::GENERAL_PRACTITIONER->value,
            'doctor_type_label' => 'Huisarts',
            'starts_at_date' => '16 mei 2026',
            'starts_at_time' => '14:30',
        ],
        new PushReminderRecipient(
            user: User::factory()->patient()->create(),
            audience: PushReminderAudience::Patient,
            openUrl: '/patient/appointments',
        ),
    );

    $message = $notification->toWebPush($notification->recipient->user, $notification);

    expect($message->toArray()['title'])->toBe(
        ReminderTranslations::trans('appointment_reminders.notification.two_days_before.title'),
    );
    expect($message->toArray()['body'])->toBe(
        ReminderTranslations::trans('appointment_reminders.notification.two_days_before.body_patient', [
            'provider' => 'City Clinic',
            'type' => 'Huisarts',
            'date' => '16 mei 2026',
            'time' => '14:30',
        ]),
    );
});
