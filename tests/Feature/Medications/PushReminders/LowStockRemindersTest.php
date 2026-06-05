<?php

use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationType;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Models\MedicationStock;
use App\Models\User;
use App\Notifications\Medications\PushReminders\LowStockNotification;
use App\Support\Medications\MedicationUrgencyToneResolver;
use App\Support\Medications\PushReminders\LowStock\ReminderTranslations;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

function seedMedicationLowStockReminderScenario(
    string $currentStock = '7',
    string $patientName = 'Sophie Maas',
): array {
    CarbonImmutable::setTestNow('2026-05-14 12:00:00');

    $patientUser = User::factory()->patient()->create(['name' => $patientName]);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $patientUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-low-stock-patient',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Magnesiumcitraat',
        'type_medication' => MedicationType::SACHETS,
    ]);

    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => $currentStock,
    ]);

    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '2',
        'dose_quantity' => '0.5',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    return [$patientUser, $patient, $medication];
}

test('low stock reminder command notifies patient at seven days supply', function () {
    Notification::fake();

    [$patientUser] = seedMedicationLowStockReminderScenario();

    Artisan::call('medication:send-low-stock-reminders');

    Notification::assertSentTo($patientUser, LowStockNotification::class, function (
        LowStockNotification $notification,
    ): bool {
        return $notification->medication['name'] === 'Magnesiumcitraat'
            && $notification->medication['supply_estimate_days'] === MedicationUrgencyToneResolver::CRITICAL_MAX_DAYS
            && $notification->medication['tier'] === 'critical'
            && $notification->recipient->openUrl === route('patient.inventory', absolute: false);
    });

    CarbonImmutable::setTestNow();
});

test('low stock reminder command notifies linked family member at seven days supply', function () {
    Notification::fake();

    [$patientUser, $patient] = seedMedicationLowStockReminderScenario();
    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $familyUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-low-stock-family',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    Artisan::call('medication:send-low-stock-reminders');

    Notification::assertSentTo($patientUser, LowStockNotification::class);
    Notification::assertSentTo($familyUser, LowStockNotification::class, function (
        LowStockNotification $notification,
    ) use ($familyUser): bool {
        $message = $notification->toWebPush($familyUser, $notification)->toArray();

        return $notification->recipient->patientName === 'Sophie Maas'
            && str_contains((string) ($message['body'] ?? ''), 'Sophie')
            && ($message['data']['openUrl'] ?? null) === route('family.medications', [
                'medication' => $notification->medication['medication_id'],
            ], absolute: false);
    });

    CarbonImmutable::setTestNow();
});

test('low stock notification uses elderly friendly dutch copy for patient', function () {
    Notification::fake();

    [$patientUser] = seedMedicationLowStockReminderScenario();

    Artisan::call('medication:send-low-stock-reminders');

    Notification::assertSentTo($patientUser, LowStockNotification::class, function (
        LowStockNotification $notification,
    ) use ($patientUser): bool {
        $message = $notification->toWebPush($patientUser, $notification)->toArray();

        return $message['title'] === ReminderTranslations::trans(
            'medication_low_stock_reminders.notification.title',
        )
            && $message['body'] === ReminderTranslations::trans(
                'medication_low_stock_reminders.notification.body_patient',
                [
                    'name' => 'Magnesiumcitraat',
                    'type' => 'Zakjes',
                    'days' => '7',
                ],
            );
    });

    CarbonImmutable::setTestNow();
});

test('low stock reminder command sends only one notification per recipient per medication', function () {
    Notification::fake();

    [$patientUser, $patient] = seedMedicationLowStockReminderScenario();
    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $familyUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/demo-low-stock-family-dedup',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    Artisan::call('medication:send-low-stock-reminders');
    Artisan::call('medication:send-low-stock-reminders');

    Notification::assertSentToTimes($patientUser, LowStockNotification::class, 1);
    Notification::assertSentToTimes($familyUser, LowStockNotification::class, 1);

    CarbonImmutable::setTestNow();
});

test('low stock reminder command skips users without push subscriptions', function () {
    Notification::fake();

    CarbonImmutable::setTestNow('2026-05-14 12:00:00');

    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '7',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '2',
        'dose_quantity' => '0.5',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    Artisan::call('medication:send-low-stock-reminders');

    Notification::assertNothingSent();

    CarbonImmutable::setTestNow();
});

test('low stock reminder command does not notify when supply estimate is above seven days', function () {
    Notification::fake();

    [$patientUser] = seedMedicationLowStockReminderScenario(currentStock: '8');

    Artisan::call('medication:send-low-stock-reminders');

    Notification::assertNothingSentTo($patientUser);

    CarbonImmutable::setTestNow();
});

test('low stock reminder command notifies when supply estimate is below seven days', function () {
    Notification::fake();

    [$patientUser] = seedMedicationLowStockReminderScenario(currentStock: '5');

    Artisan::call('medication:send-low-stock-reminders');

    Notification::assertSentToTimes($patientUser, LowStockNotification::class, 1);
    Notification::assertSentTo($patientUser, LowStockNotification::class, function (
        LowStockNotification $notification,
    ): bool {
        return $notification->medication['supply_estimate_days'] === 5
            && $notification->medication['tier'] === 'critical';
    });

    CarbonImmutable::setTestNow();
});

test('low stock reminder command sends urgent tier at two days supply', function () {
    Notification::fake();

    [$patientUser] = seedMedicationLowStockReminderScenario(currentStock: '2');

    Artisan::call('medication:send-low-stock-reminders');

    Notification::assertSentToTimes($patientUser, LowStockNotification::class, 2);
    Notification::assertSentTo($patientUser, LowStockNotification::class, function (
        LowStockNotification $notification,
    ): bool {
        return $notification->medication['tier'] === 'urgent'
            && $notification->medication['supply_estimate_days'] === MedicationUrgencyToneResolver::REMINDER_URGENT_MAX_DAYS;
    });

    CarbonImmutable::setTestNow();
});

test('low stock urgent tier is sent after critical tier was already delivered', function () {
    Notification::fake();

    [$patientUser, $patient, $medication] = seedMedicationLowStockReminderScenario(currentStock: '7');

    Artisan::call('medication:send-low-stock-reminders');
    Notification::assertSentToTimes($patientUser, LowStockNotification::class, 1);

    $stock = $medication->stocks()->first();
    expect($stock)->not->toBeNull();
    $stock->update(['current_stock' => '2']);

    Notification::fake();

    Artisan::call('medication:send-low-stock-reminders');

    Notification::assertSentToTimes($patientUser, LowStockNotification::class, 1);
    Notification::assertSentTo($patientUser, LowStockNotification::class, function (
        LowStockNotification $notification,
    ): bool {
        return $notification->medication['tier'] === 'urgent';
    });

    CarbonImmutable::setTestNow();
});

test('low stock reminder cache resets after stock is refilled above seven days', function () {
    Notification::fake();

    [$patientUser, $patient, $medication] = seedMedicationLowStockReminderScenario();

    Artisan::call('medication:send-low-stock-reminders');
    Notification::assertSentToTimes($patientUser, LowStockNotification::class, 1);

    $stock = $medication->stocks()->first();
    expect($stock)->not->toBeNull();

    $stock->update(['current_stock' => '30']);

    Notification::fake();

    Artisan::call('medication:send-low-stock-reminders');
    Notification::assertNothingSentTo($patientUser);

    $stock->update(['current_stock' => '7']);

    Artisan::call('medication:send-low-stock-reminders');
    Notification::assertSentToTimes($patientUser, LowStockNotification::class, 1);

    CarbonImmutable::setTestNow();
});

test('family member can store push subscription', function () {
    $patientUser = User::factory()->patient()->create();
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $familyUser = createLinkedFamilyMemberForPatient($patient);

    $this->actingAs($familyUser)
        ->postJson(route('family.push-subscriptions.store'), [
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/family-store-test',
            'keys' => [
                'p256dh' => 'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
                'auth' => 'tBHItJI5svbpez7KI4CCXg',
            ],
            'contentEncoding' => 'aesgcm',
        ])
        ->assertOk()
        ->assertJson(['stored' => true]);

    expect($familyUser->pushSubscriptions()->count())->toBe(1);
});

test('family overview shares webpush props for subscribed family member', function () {
    $familyUser = User::factory()->familyMember()->create();

    $familyUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/family-overview-webpush',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    $this->actingAs($familyUser)
        ->get(route('family.overview'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('webpush.subscribed', true));
});
