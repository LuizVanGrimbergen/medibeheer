<?php

use App\Enums\MedicationPrescriptionPickupStatus;
use App\Enums\MedicationType;
use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Models\User;
use App\Notifications\Medications\PushReminders\PrescriptionExpiryNotification;
use App\Support\Medications\MedicationUrgencyToneResolver;
use App\Support\PushReminders\ReminderTranslations;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

function seedPrescriptionExpiryReminderScenario(
    string $expiryDate = '2026-05-21',
    string $patientName = 'Sophie Maas',
): array {
    CarbonImmutable::setTestNow('2026-05-14 12:00:00');

    $patientUser = User::factory()->patient()->create(['name' => $patientName]);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $patientUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-prescription-expiry-patient',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Metformine',
        'type_medication' => MedicationType::PILL,
    ]);

    $prescription = MedicationPrescription::factory()
        ->forMedication($medication)
        ->create([
            'prescription_expiry_date' => $expiryDate,
            'pickup_status' => MedicationPrescriptionPickupStatus::PICKED_UP,
        ]);

    return [$patientUser, $patient, $medication, $prescription];
}

test('prescription expiry reminder command notifies patient at seven days', function () {
    Notification::fake();

    [$patientUser] = seedPrescriptionExpiryReminderScenario();

    Artisan::call('medication:send-prescription-expiry-reminders');

    Notification::assertSentTo($patientUser, PrescriptionExpiryNotification::class, function (
        PrescriptionExpiryNotification $notification,
    ): bool {
        return $notification->prescription['name'] === 'Metformine'
            && $notification->prescription['days_remaining'] === MedicationUrgencyToneResolver::CRITICAL_MAX_DAYS
            && $notification->prescription['tier'] === 'critical'
            && $notification->recipient->openUrl === route('patient.prescriptions', absolute: false);
    });

    CarbonImmutable::setTestNow();
});

test('prescription expiry reminder command notifies linked family member', function () {
    Notification::fake();

    [$patientUser, $patient, $medication] = seedPrescriptionExpiryReminderScenario();
    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $familyUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-prescription-expiry-family',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    Artisan::call('medication:send-prescription-expiry-reminders');

    Notification::assertSentTo($patientUser, PrescriptionExpiryNotification::class);
    Notification::assertSentTo($familyUser, PrescriptionExpiryNotification::class, function (
        PrescriptionExpiryNotification $notification,
    ) use ($familyUser, $medication): bool {
        $message = $notification->toWebPush($familyUser, $notification)->toArray();

        return $notification->recipient->patientName === 'Sophie Maas'
            && str_contains((string) ($message['body'] ?? ''), 'Sophie')
            && ($message['data']['openUrl'] ?? null) === route('family.medications', [
                'medication' => $medication->id,
            ], absolute: false);
    });

    CarbonImmutable::setTestNow();
});

test('prescription expiry notification uses elderly friendly dutch copy for patient', function () {
    Notification::fake();

    [$patientUser] = seedPrescriptionExpiryReminderScenario();

    Artisan::call('medication:send-prescription-expiry-reminders');

    Notification::assertSentTo($patientUser, PrescriptionExpiryNotification::class, function (
        PrescriptionExpiryNotification $notification,
    ) use ($patientUser): bool {
        $message = $notification->toWebPush($patientUser, $notification)->toArray();

        return $message['title'] === ReminderTranslations::trans(
            'prescription_expiry_reminders.notification.title',
        )
            && $message['body'] === ReminderTranslations::trans(
                'prescription_expiry_reminders.notification.body_patient',
                [
                    'name' => 'Metformine',
                    'type' => 'Tablet / pil',
                    'days' => '7',
                ],
            );
    });

    CarbonImmutable::setTestNow();
});

test('prescription expiry reminder command sends only one notification per recipient per prescription', function () {
    Notification::fake();

    [$patientUser, $patient] = seedPrescriptionExpiryReminderScenario();
    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $familyUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-prescription-expiry-family-dedup',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    Artisan::call('medication:send-prescription-expiry-reminders');
    Artisan::call('medication:send-prescription-expiry-reminders');

    Notification::assertSentToTimes($patientUser, PrescriptionExpiryNotification::class, 1);
    Notification::assertSentToTimes($familyUser, PrescriptionExpiryNotification::class, 1);

    CarbonImmutable::setTestNow();
});

test('prescription expiry reminder command skips users without push subscriptions', function () {
    Notification::fake();

    CarbonImmutable::setTestNow('2026-05-14 12:00:00');

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();
    MedicationPrescription::factory()
        ->forMedication($medication)
        ->create([
            'prescription_expiry_date' => '2026-05-21',
        ]);

    Artisan::call('medication:send-prescription-expiry-reminders');

    Notification::assertNothingSent();

    CarbonImmutable::setTestNow();
});

test('prescription expiry reminder command does not notify when expiry is only a warning', function () {
    Notification::fake();

    [$patientUser] = seedPrescriptionExpiryReminderScenario(expiryDate: '2026-05-28');

    Artisan::call('medication:send-prescription-expiry-reminders');

    Notification::assertNothingSentTo($patientUser);

    CarbonImmutable::setTestNow();
});

test('prescription expiry reminder command notifies when expiry is below seven days', function () {
    Notification::fake();

    [$patientUser] = seedPrescriptionExpiryReminderScenario(expiryDate: '2026-05-19');

    Artisan::call('medication:send-prescription-expiry-reminders');

    Notification::assertSentToTimes($patientUser, PrescriptionExpiryNotification::class, 1);
    Notification::assertSentTo($patientUser, PrescriptionExpiryNotification::class, function (
        PrescriptionExpiryNotification $notification,
    ): bool {
        return $notification->prescription['days_remaining'] === 5
            && $notification->prescription['tier'] === 'critical';
    });

    CarbonImmutable::setTestNow();
});

test('prescription expiry reminder command sends urgent tier at two days', function () {
    Notification::fake();

    [$patientUser] = seedPrescriptionExpiryReminderScenario(expiryDate: '2026-05-16');

    Artisan::call('medication:send-prescription-expiry-reminders');

    Notification::assertSentToTimes($patientUser, PrescriptionExpiryNotification::class, 2);
    Notification::assertSentTo($patientUser, PrescriptionExpiryNotification::class, function (
        PrescriptionExpiryNotification $notification,
    ): bool {
        return $notification->prescription['tier'] === 'urgent'
            && $notification->prescription['days_remaining'] === MedicationUrgencyToneResolver::REMINDER_URGENT_MAX_DAYS;
    });

    CarbonImmutable::setTestNow();
});

test('prescription expiry urgent tier is sent after critical tier was already delivered', function () {
    Notification::fake();

    [$patientUser, $patient, $medication, $prescription] = seedPrescriptionExpiryReminderScenario();

    Artisan::call('medication:send-prescription-expiry-reminders');
    Notification::assertSentToTimes($patientUser, PrescriptionExpiryNotification::class, 1);

    $prescription->update(['prescription_expiry_date' => '2026-05-16']);

    Notification::fake();

    Artisan::call('medication:send-prescription-expiry-reminders');

    Notification::assertSentToTimes($patientUser, PrescriptionExpiryNotification::class, 1);
    Notification::assertSentTo($patientUser, PrescriptionExpiryNotification::class, function (
        PrescriptionExpiryNotification $notification,
    ): bool {
        return $notification->prescription['tier'] === 'urgent';
    });

    CarbonImmutable::setTestNow();
});

test('prescription expiry reminder cache resets after expiry is extended beyond seven days', function () {
    Notification::fake();

    [$patientUser, $patient, $medication, $prescription] = seedPrescriptionExpiryReminderScenario();

    Artisan::call('medication:send-prescription-expiry-reminders');
    Notification::assertSentToTimes($patientUser, PrescriptionExpiryNotification::class, 1);

    $prescription->update(['prescription_expiry_date' => '2026-12-31']);

    Notification::fake();

    Artisan::call('medication:send-prescription-expiry-reminders');
    Notification::assertNothingSentTo($patientUser);

    $prescription->update(['prescription_expiry_date' => '2026-05-21']);

    Artisan::call('medication:send-prescription-expiry-reminders');
    Notification::assertSentToTimes($patientUser, PrescriptionExpiryNotification::class, 1);

    CarbonImmutable::setTestNow();
});

test('prescription expiry reminder cache resets when prescription is completed', function () {
    Notification::fake();

    [$patientUser, $patient, $medication, $prescription] = seedPrescriptionExpiryReminderScenario();

    Artisan::call('medication:send-prescription-expiry-reminders');
    Notification::assertSentToTimes($patientUser, PrescriptionExpiryNotification::class, 1);

    $prescription->update(['completed_at' => now()]);

    Notification::fake();

    Artisan::call('medication:send-prescription-expiry-reminders');
    Notification::assertNothingSentTo($patientUser);

    CarbonImmutable::setTestNow();
});
