<?php

use App\Enums\MedicationIntakeFrequency;
use App\Models\Family;
use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Models\MedicationSchedule;
use App\Models\MedicationStock;
use App\Models\Patient;
use App\Models\User;
use App\Services\Medications\MedicationVacationSupplyService;
use Carbon\Carbon;
use Database\Seeders\MedicationSeeder;
use Database\Seeders\PatientWebPushDemoSeeder;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-05-14 12:00:00', 'Europe/Amsterdam'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('calculates pickup quantity for daily intake over a vacation period', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create([
        'stock_pieces_per_package' => 30,
    ]);
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '5',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '2',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $result = app(MedicationVacationSupplyService::class)->buildPickupList(
        $patient,
        Carbon::parse('2026-05-15'),
        Carbon::parse('2026-05-17'),
    );

    expect($result['vacation_days'])->toBe(3);
    expect($result['items'])->toHaveCount(1);
    expect($result['items'][0]['pickup_quantity'])->toBe('1');
    expect($result['items'][0]['needed_for_period'])->toBe('6');
    expect($result['items'][0]['stock_pieces_per_package'])->toBe(30);
});

it('returns no pickup items when current stock covers the vacation period', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '30',
    ]);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $result = app(MedicationVacationSupplyService::class)->buildPickupList(
        $patient,
        Carbon::parse('2026-05-15'),
        Carbon::parse('2026-05-17'),
    );

    expect($result['items'])->toBe([]);
});

it('counts only weekday intakes inside the vacation range', function () {
    $patient = Patient::factory()->create();
    $medication = Medication::factory()->for($patient)->create();
    MedicationStock::factory()->forMedication($medication)->create([
        'current_stock' => '0',
    ]);
    $weekdaySchedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::WEEKDAYS,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subWeek(),
        'end_date' => now()->addMonth(),
    ]);
    $weekdaySchedule->syncIntakeWeekdays([1, 2, 3, 4, 5]);

    $result = app(MedicationVacationSupplyService::class)->buildPickupList(
        $patient,
        Carbon::parse('2026-05-11'),
        Carbon::parse('2026-05-17'),
    );

    expect($result['items'])->toHaveCount(1);
    expect($result['items'][0]['needed_for_period'])->toBe('5');
});

it('patients can open the vacation supply page from inventory', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->get(route('patient.inventory.vacation'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Inventory/Vacation');
    $response->assertInertia(fn ($page) => $page
        ->component('Patient/Inventory/Vacation')
        ->where('starts_on', '')
        ->where('ends_on', '')
        ->where('result', null)
        ->has('expiring_prescriptions', 0));
});

it('includes expiring prescriptions on vacation results', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $expiringMedication = Medication::factory()
        ->for($patient)
        ->has(MedicationStock::factory(), 'stocks')
        ->create([
            'stock_pieces_per_package' => 12,
        ]);
    MedicationSchedule::factory()->forMedication($expiringMedication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '1',
        'dose_quantity' => '1',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);
    MedicationPrescription::factory()
        ->forMedication($expiringMedication)
        ->create([
            'prescription_expiry_date' => '2026-05-19',
        ]);

    $safeMedication = Medication::factory()->for($patient)->create();
    MedicationPrescription::factory()
        ->forMedication($safeMedication)
        ->create([
            'prescription_expiry_date' => '2026-08-01',
        ]);

    $expiringMedication->stocks()->first()?->update(['current_stock' => '0']);

    $response = $this->actingAs($user)->post(route('patient.inventory.vacation.store'), [
        'starts_on' => '2026-05-15',
        'ends_on' => '2026-05-16',
    ]);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Patient/Inventory/Vacation')
        ->has('expiring_prescriptions', 1)
        ->where('expiring_prescriptions.0.medication_name', $expiringMedication->name)
        ->where('expiring_prescriptions.0.days_remaining', 5));
});

it('patients can calculate vacation supply on a dedicated page', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()
        ->for($patient)
        ->has(MedicationStock::factory(), 'stocks')
        ->create([
            'stock_pieces_per_package' => 12,
        ]);

    MedicationSchedule::factory()->forMedication($medication)->create([
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '1',
        'dose_quantity' => '2',
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $stock = $medication->stocks()->first();
    expect($stock)->not->toBeNull();
    $stock->update(['current_stock' => '1']);

    $response = $this->actingAs($user)->post(route('patient.inventory.vacation.store'), [
        'starts_on' => '2026-05-15',
        'ends_on' => '2026-05-16',
    ]);

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Inventory/Vacation');
    $response->assertInertia(fn ($page) => $page
        ->component('Patient/Inventory/Vacation')
        ->where('starts_on', '2026-05-15')
        ->where('ends_on', '2026-05-16')
        ->where('result.vacation_days', 2)
        ->where('result.items.0.pickup_quantity', '3')
        ->where('result.items.0.stock_pieces_per_package', 12)
        ->where('result.items.0.name', $medication->name));
});

it('rejects vacation periods longer than 366 days', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->from(route('patient.inventory.vacation'))
        ->post(route('patient.inventory.vacation.store'), [
            'starts_on' => '2026-05-15',
            'ends_on' => '2027-05-16',
        ]);

    $response->assertSessionHasErrors('ends_on');
});

it('demo seeders leave no medications skipped for a typical vacation window', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $family = Family::factory()->create();
    $patient->families()->attach($family);

    (new MedicationSeeder)->run($patient, $family);
    (new PatientWebPushDemoSeeder)->run($user);

    $result = app(MedicationVacationSupplyService::class)->buildPickupList(
        $patient,
        Carbon::parse('2026-06-07'),
        Carbon::parse('2026-06-28'),
    );

    expect($result['skipped_medication_count'])->toBe(0);
});

it('rejects vacation start dates in the past', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->from(route('patient.inventory.vacation'))
        ->post(route('patient.inventory.vacation.store'), [
            'starts_on' => '2026-05-10',
            'ends_on' => '2026-05-20',
        ]);

    $response->assertSessionHasErrors('starts_on');
});
