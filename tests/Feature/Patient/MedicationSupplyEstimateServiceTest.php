<?php

use App\Enums\MedicationIntakeFrequency;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Models\MedicationStock;
use App\Models\Patient;
use App\Services\Medications\MedicationSupplyEstimateService;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-05-14 12:00:00', 'Europe/Amsterdam'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('estimates days for daily intake from stock and schedule', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '10',
        'low_stock' => '2',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'intake_weekdays' => null,
        'times_per_day' => '2',
        'dose_quantity' => '0.5',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $medication->load(['stocks', 'schedules']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(10);
});

it('averages every n days intake over calendar days', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '14',
        'low_stock' => '1',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::everyNDaysValue(7),
        'intake_weekdays' => null,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $medication->load(['stocks', 'schedules']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(98);
});

it('averages weekday intake over seven days', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '10',
        'low_stock' => '1',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::WEEKDAYS,
        'intake_weekdays' => [1, 2, 3, 4, 5],
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $medication->load(['stocks', 'schedules']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(14);
});

it('returns unknown when no schedules exist', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '10',
        'low_stock' => '2',
    ]);

    $medication->load(['stocks', 'schedules']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('unknown');
    expect($result['days'])->toBeNull();
});

it('returns unknown when schedule is outside the active date range', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '10',
        'low_stock' => '2',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'intake_weekdays' => null,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subMonths(3),
        'end_date' => now()->subMonth(),
    ]);

    $medication->load(['stocks', 'schedules']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('unknown');
    expect($result['days'])->toBeNull();
});

it('parses decimal stock with comma', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '3,5',
        'low_stock' => '1',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'intake_weekdays' => null,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $medication->load(['stocks', 'schedules']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(4);
});

it('returns zero days when rounded supply is less than one day at the estimated rate', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '1',
        'low_stock' => '1',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'intake_weekdays' => null,
        'times_per_day' => '3',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $medication->load(['stocks', 'schedules']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(0);
});
