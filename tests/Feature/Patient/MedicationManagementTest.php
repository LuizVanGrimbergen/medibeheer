<?php

use App\Enums\MedicationColor;
use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationType;
use App\Models\Family;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Models\MedicationStock;
use App\Models\User;
use Database\Seeders\MedicationSeeder;
use Illuminate\Support\Facades\DB;

/** @return array<string, mixed> */
function validNewMedicationSchedulePayload(): array
{
    return [
        'meal_timing' => MedicationMealTiming::WITH_FOOD->value,
        'intake_frequency' => MedicationIntakeFrequency::DAILY,
        'times_per_day' => '1',
        'dose_time' => '09:00',
        'start_date' => '2026-05-01',
        'end_date' => '2026-05-07',
    ];
}

function validNewMedicationStockPayload(): array
{
    return [
        'current_stock' => '20',
        'low_stock' => '5',
    ];
}

test('patient medications inertia page includes medications collection', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->get(route('patient.medications'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Medications');
    $response->assertInertia(fn ($page) => $page
        ->component('Patient/Medications')
        ->has('medications.data')
        ->has('medications.meta')
        ->where('can_create_medication', true));
});

test('patients can create a medication and name is encrypted at rest', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Ibuprofen',
        'dose' => '400',
        'dose_unit' => MedicationDoseUnit::MILLIGRAM->value,
        'type_medication' => MedicationType::PILL->value,
        'color' => MedicationColor::RED->value,
        ...validNewMedicationStockPayload(),
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertRedirect(route('patient.medications'));

    $medication = Medication::query()->where('patient_id', $patient->id)->first();
    expect($medication)->not->toBeNull();
    expect($medication->name)->toBe('Ibuprofen');
    expect($medication->dose)->toBe('400');
    expect($medication->dose_unit)->toBe(MedicationDoseUnit::MILLIGRAM);
    expect($medication->color)->toBe(MedicationColor::RED);
    expect($medication->family_id)->toBeNull();

    $schedule = MedicationSchedule::query()->where('medication_id', $medication->id)->first();
    expect($schedule)->not->toBeNull();
    expect($schedule->meal_timing)->toBe(MedicationMealTiming::WITH_FOOD);
    expect($schedule->intake_frequency)->toBe(MedicationIntakeFrequency::DAILY);
    expect($schedule->intake_weekdays)->toBeNull();
    expect($schedule->times_per_day)->toBe('1');
    expect($schedule->dose_quantity)->toBe('400');
    expect($schedule->dose_time)->toBe('09:00');
    expect($schedule->start_date?->format('Y-m-d'))->toBe('2026-05-01');
    expect($schedule->end_date?->format('Y-m-d'))->toBe('2026-05-07');

    $stock = MedicationStock::query()->where('medication_id', $medication->id)->first();
    expect($stock)->not->toBeNull();
    expect($stock->current_stock)->toBe('20');
    expect($stock->low_stock)->toBe('5');

    $raw = DB::table('medications')->where('id', $medication->id)->first();
    expect($raw->name)->not->toBe('Ibuprofen');
    expect($raw->dose)->not->toBe('400');
});

test('patients can create a medication with an optional trimmed note', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Aspirine',
        'dose' => '100',
        'dose_unit' => MedicationDoseUnit::MILLIGRAM->value,
        'type_medication' => MedicationType::PILL->value,
        'color' => MedicationColor::BLUE->value,
        'note' => '  Na het eten innemen  ',
        ...validNewMedicationStockPayload(),
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertRedirect(route('patient.medications'));

    $medication = Medication::query()->where('patient_id', $patient->id)->first();
    expect($medication)->not->toBeNull();
    expect($medication->note)->toBe('Na het eten innemen');

    $raw = DB::table('medications')->where('id', $medication->id)->first();
    expect($raw->note)->not->toBe('Na het eten innemen');
});

test('patients cannot store a medication without stock fields', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Zonder voorraad',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        'color' => MedicationColor::BLUE->value,
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertSessionHasErrors(['current_stock', 'low_stock']);
});

test('patients cannot store a medication with only one stock field', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Alleen drempel',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        'color' => MedicationColor::BLUE->value,
        'low_stock' => '3',
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertSessionHasErrors('current_stock');
});

test('patients cannot store a medication with only current stock', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Alleen huidige voorraad',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        'color' => MedicationColor::BLUE->value,
        'current_stock' => '10',
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertSessionHasErrors('low_stock');
});

test('patients creating a medication links it to the first linked family when present', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $family = Family::factory()->create();
    $patient->families()->syncWithoutDetaching([$family->id]);

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Vitamine C',
        'dose' => '1000',
        'dose_unit' => MedicationDoseUnit::MILLIGRAM->value,
        'type_medication' => MedicationType::PILL->value,
        'color' => MedicationColor::BLUE->value,
        ...validNewMedicationStockPayload(),
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertRedirect(route('patient.medications'));

    $medication = Medication::query()->where('patient_id', $patient->id)->first();
    expect($medication)->not->toBeNull();
    expect($medication->family_id)->toBe($family->id);

    $schedule = MedicationSchedule::query()->where('medication_id', $medication->id)->first();
    expect($schedule)->not->toBeNull();
    expect($schedule->family_id)->toBe($family->id);
});

test('patients cannot store a medication without dose', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Zonder dosis',
        'dose' => '',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        'color' => MedicationColor::BLUE->value,
        ...validNewMedicationStockPayload(),
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertSessionHasErrors('dose');
});

test('patients cannot store a medication without dose unit', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Zonder eenheid',
        'dose' => '10',
        'type_medication' => MedicationType::LIQUID->value,
        'color' => MedicationColor::BLUE->value,
        ...validNewMedicationStockPayload(),
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertSessionHasErrors('dose_unit');
});

test('patients cannot store a new medication without a schedule', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Zonder schema',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        'color' => MedicationColor::BLUE->value,
        ...validNewMedicationStockPayload(),
    ]);

    $response->assertSessionHasErrors('schedule');
});

test('patients store schedule dose quantity from medication dose', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $schedule = validNewMedicationSchedulePayload();
    $schedule['dose_quantity'] = '999';

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Gekoppelde hoeveelheid',
        'dose' => '250',
        'dose_unit' => MedicationDoseUnit::MILLIGRAM->value,
        'type_medication' => MedicationType::PILL->value,
        'color' => MedicationColor::BLUE->value,
        ...validNewMedicationStockPayload(),
        'schedule' => $schedule,
    ]);

    $response->assertRedirect(route('patient.medications'));

    $medication = Medication::query()->where('patient_id', $patient->id)->first();
    expect($medication)->not->toBeNull();
    expect($medication->dose)->toBe('250');

    $saved = MedicationSchedule::query()->where('medication_id', $medication->id)->first();
    expect($saved)->not->toBeNull();
    expect($saved->dose_quantity)->toBe('250');
});

test('patients can add a schedule with encrypted dose fields', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create([
        'name' => 'Vitamine D',
        'dose' => '2',
        'dose_unit' => MedicationDoseUnit::DROP,
        'type_medication' => MedicationType::LIQUID,
    ]);

    $response = $this->actingAs($user)->post(
        route('patient.medications.schedules.store', $medication),
        [
            'meal_timing' => MedicationMealTiming::WITH_FOOD->value,
            'intake_frequency' => MedicationIntakeFrequency::DAILY,
            'times_per_day' => '1',
            'dose_time' => '09:00',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-01',
        ],
    );

    $response->assertRedirect(route('patient.medications'));

    $schedule = MedicationSchedule::query()->where('medication_id', $medication->id)->first();
    expect($schedule)->not->toBeNull();
    expect($schedule->dose_quantity)->toBe('2');

    $raw = DB::table('medication_schedules')->where('id', $schedule->id)->first();
    expect($raw->dose_quantity)->not->toBe('');
    expect($raw->dose_time)->not->toBe('09:00');
    expect($raw->times_per_day)->not->toBe('1');
});

test('patients can create a medication with weekday-only intake frequency', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $schedule = validNewMedicationSchedulePayload();
    $schedule['intake_frequency'] = MedicationIntakeFrequency::WEEKDAYS;
    $schedule['intake_weekdays'] = [1, 2];

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Weekschema',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        'color' => MedicationColor::BLUE->value,
        ...validNewMedicationStockPayload(),
        'schedule' => $schedule,
    ]);

    $response->assertRedirect(route('patient.medications'));

    $medication = Medication::query()->where('patient_id', $patient->id)->first();
    expect($medication)->not->toBeNull();

    $saved = MedicationSchedule::query()->where('medication_id', $medication->id)->first();
    expect($saved)->not->toBeNull();
    expect($saved->intake_frequency)->toBe(MedicationIntakeFrequency::WEEKDAYS);
    expect($saved->intake_weekdays)->toBe([1, 2]);
});

test('doctors cannot create patient medications', function () {
    $user = User::factory()->doctor()->create();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Test',
        'type_medication' => MedicationType::PILL->value,
        'color' => null,
    ]);

    $response->assertForbidden();
});

test('medication seeder persists encrypted schedule and stock fields', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $family = Family::factory()->create();
    $patient->families()->syncWithoutDetaching([$family->id]);

    (new MedicationSeeder)->run($patient, $family);

    $medications = $patient->medications()->orderBy('id')->get();
    expect($medications)->toHaveCount(2);

    $medication = $medications->first();
    expect($medication->name)->toBe('Paracetamol 500 mg');
    expect($medication->dose)->toBe('1');
    expect($medication->dose_unit)->toBe(MedicationDoseUnit::PIECE);
    expect($medication->color)->toBe(MedicationColor::BLUE);
    expect($medication->note)->toBe('Bij koorts extra letten op vocht.');
    expect($medication->family_id)->toBe($family->id);

    $schedule = $medication->schedules()->first();
    expect($schedule)->not->toBeNull();
    expect($schedule->times_per_day)->toBe('3');
    expect($schedule->family_id)->toBe($family->id);

    $rawSchedule = DB::table('medication_schedules')->where('id', $schedule->id)->first();
    expect($rawSchedule->times_per_day)->not->toBe('3');
    expect($rawSchedule->dose_time)->not->toBe('08:00, 14:00, 21:00');

    $stock = $medication->stocks()->first();
    expect($stock)->not->toBeNull();
    expect($stock->low_stock)->toBe('10');
    expect($stock->family_id)->toBe($family->id);

    $rawStock = DB::table('medication_stocks')->where('id', $stock->id)->first();
    expect($rawStock->low_stock)->not->toBe('10');

    $metformin = $medications->get(1);
    expect($metformin->name)->toBe('Metformine');
    expect($metformin->dose)->toBe('500');
    expect($metformin->dose_unit)->toBe(MedicationDoseUnit::MILLIGRAM);
    expect($metformin->family_id)->toBe($family->id);

    $metforminStockRaw = DB::table('medication_stocks')->where('medication_id', $metformin->id)->first();
    expect($metforminStockRaw->current_stock)->not->toBe('28');
});

test('patients cannot update another patients medication', function () {
    $owner = User::factory()->patient()->create();
    $other = User::factory()->patient()->create();
    expect($owner->patient)->not->toBeNull();
    expect($other->patient)->not->toBeNull();

    $medication = Medication::factory()->for($owner->patient)->create([
        'name' => 'Geheim',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE,
        'type_medication' => MedicationType::PILL,
    ]);

    $response = $this->actingAs($other)->put(
        route('patient.medications.update', $medication),
        [
            'name' => 'Gestolen',
            'type_medication' => MedicationType::PILL->value,
        ],
    );

    $response->assertForbidden();
});
