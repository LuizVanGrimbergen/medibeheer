<?php

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Models\User;
use App\Support\AppClock;
use Carbon\CarbonImmutable;

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('preview two day appointment reminders lists matching appointments and recipients', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-14 09:00:00', AppClock::TIMEZONE),
    );

    $patientUser = User::factory()->patient()->create(['name' => 'Sophie Maas']);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $patientUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/preview-two-day',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    Appointment::factory()->for($patient)->create([
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER,
        'provider_name' => 'City Clinic',
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => '2026-05-16 14:30:00',
    ]);

    $this->artisan('appointment:preview-two-day-reminders')
        ->expectsOutputToContain('Looking for scheduled appointments on 2026-05-16.')
        ->expectsOutputToContain('City Clinic')
        ->expectsOutputToContain('Sophie Maas')
        ->expectsOutputToContain('would send')
        ->assertSuccessful();
});

test('preview two day appointment reminders warns when no appointments match', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-14 09:00:00', AppClock::TIMEZONE),
    );

    $this->artisan('appointment:preview-two-day-reminders')
        ->expectsOutputToContain('No scheduled appointments two calendar days from today.')
        ->assertSuccessful();
});

test('preview two hour appointment reminders lists matching appointments and recipients', function () {
    CarbonImmutable::setTestNow(
        CarbonImmutable::parse('2026-05-16 12:30:00', AppClock::TIMEZONE),
    );

    $patientUser = User::factory()->patient()->create(['name' => 'Sophie Maas']);
    $patient = $patientUser->patient;
    expect($patient)->not->toBeNull();

    $patientUser->updatePushSubscription(
        'https://updates.push.services.mozilla.com/wpush/v2/preview-two-hour',
        'BNcRdxfALFjixSmx2EPhyCDiFxHk4Tc09v99d5LOBqWVXa9Wf9jDhtHW1vJYqY2WTNfbk5dVBGt8Ar0H1uY2B8',
        'tBHItJI5svbpez7KI4CCXg',
    );

    Appointment::factory()->for($patient)->create([
        'doctor_type' => DoctorType::GENERAL_PRACTITIONER,
        'provider_name' => 'City Clinic',
        'status' => AppointmentStatus::SCHEDULED,
        'starts_at' => '2026-05-16 14:30:00',
    ]);

    $this->artisan('appointment:preview-two-hour-reminders')
        ->expectsOutputToContain('Looking for scheduled appointments starting at 2026-05-16 14:30.')
        ->expectsOutputToContain('City Clinic')
        ->expectsOutputToContain('Sophie Maas')
        ->expectsOutputToContain('would send')
        ->assertSuccessful();
});
