<?php

use App\Enums\MedicationDoseUnit;
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
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '2',
        'dose_quantity' => '0.5',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $medication->load(['stocks', 'schedules.weekdays']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(10);
});

it('averages every n days intake over calendar days', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '14',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::everyNDaysValue(7),
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $medication->load(['stocks', 'schedules.weekdays']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(98);
});

it('averages selected weekday intake over seven days', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '10',
    ]);
    $weekdaySchedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::WEEKDAYS,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);
    $weekdaySchedule->syncIntakeWeekdays([1, 2, 3, 4, 5]);

    $medication->load(['stocks', 'schedules.weekdays']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(14);
});

it('averages two selected weekdays over seven days', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '10',
    ]);
    $twoDaySchedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::WEEKDAYS,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);
    $twoDaySchedule->syncIntakeWeekdays([1, 2]);

    $medication->load(['stocks', 'schedules.weekdays']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(35);
});

it('returns unknown when no schedules exist', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '10',
    ]);

    $medication->load(['stocks', 'schedules.weekdays']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('unknown');
    expect($result['days'])->toBeNull();
});

it('includes the schedule end date on the last active day', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '10',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->toDateString(),
    ]);

    $medication->load(['stocks', 'schedules.weekdays']);
    $schedule = $medication->schedules->first();
    $schedule->end_date = Carbon::parse('2026-05-14 23:59:59', 'Europe/Amsterdam');

    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(10);
});

it('returns unknown when schedule is outside the active date range', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '10',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subMonths(3),
        'end_date' => now()->subMonth(),
    ]);

    $medication->load(['stocks', 'schedules.weekdays']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('unknown');
    expect($result['days'])->toBeNull();
});

it('parses decimal stock with comma', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '3,5',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $medication->load(['stocks', 'schedules.weekdays']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(3);
});

it('parses stock with piece unit suffix for supply estimate', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create([
        'dose_unit' => MedicationDoseUnit::PIECE,
    ]);
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '30 stuks',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '1',
        'dose_quantity' => '2',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $medication->load(['stocks', 'schedules.weekdays']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(15);
});

it('returns zero days when floored supply is less than one day at the estimated rate', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '1',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '3',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $medication->load(['stocks', 'schedules.weekdays']);
    $result = app(MedicationSupplyEstimateService::class)->estimate($medication);

    expect($result['quality'])->toBe('approx');
    expect($result['days'])->toBe(0);
});
