<?php

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationListStatus;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationPrescriptionPickupStatus;
use App\Enums\MedicationType;
use App\Models\Family;
use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Models\MedicationSchedule;
use App\Models\MedicationScheduleWeekday;
use App\Models\MedicationStock;
use App\Models\User;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationListClassifier;
use Carbon\CarbonImmutable;
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
        'snooze_time' => '30',
        'start_date' => '2026-05-01',
        'end_date' => '2026-05-07',
    ];
}

function validNewMedicationStockPayload(): array
{
    return [
        'current_stock' => '20',
        'stock_pieces_per_package' => 10,
    ];
}

test('medication list classifier marks active medications without an end date', function () {
    CarbonImmutable::setTestNow('2026-05-19 10:00:00');

    $patient = User::factory()->patient()->create()->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();
    MedicationSchedule::factory()->forMedication($medication)->create([
        'end_date' => null,
    ]);

    $status = app(MedicationListClassifier::class)->statusFor($medication->fresh('schedules'));

    expect($status)->toBe(MedicationListStatus::ACTIVE);
});

test('medication list classifier marks medications past end date as ended', function () {
    CarbonImmutable::setTestNow('2026-05-19 10:00:00');

    $patient = User::factory()->patient()->create()->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();
    MedicationSchedule::factory()->forMedication($medication)->create([
        'end_date' => MedicationIntakeClock::today()->subDay()->toDateString(),
    ]);

    $status = app(MedicationListClassifier::class)->statusFor($medication->fresh('schedules'));

    expect($status)->toBe(MedicationListStatus::ENDED);
});

test('patient medications inertia page includes medications collection', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->get(route('patient.medications'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Medications/Index');
    $response->assertInertia(fn ($page) => $page
        ->component('Patient/Medications/Index')
        ->where('can_create_medication', true));
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->has('active_medications.data')
        ->has('active_medications.meta')));
});

test('patient medications inertia payload omits redundant medication relation ids', function () {
    CarbonImmutable::setTestNow('2026-05-19 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create(['name' => 'Metformine']);
    MedicationSchedule::factory()->forMedication($medication)->create([
        'end_date' => '2026-12-31',
    ]);
    MedicationStock::factory()->for($medication)->create();

    $response = $this->actingAs($user)->get(route('patient.medications'));

    $response->assertOk();
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->has('active_medications.data', 1)
        ->missing('active_medications.data.0.patient_id')
        ->missing('active_medications.data.0.schedules.0.id')
        ->missing('active_medications.data.0.schedules.0.medication_id')
        ->missing('active_medications.data.0.schedules.0.dose_quantity')
        ->missing('active_medications.data.0.stocks.0.medication_id')
        ->has('active_medications.data.0.stocks.0.id')
        ->has('active_medications.data.0.stocks.0.current_stock')));
});

test('patient medications page splits active and previously used medications', function () {
    CarbonImmutable::setTestNow('2026-05-19 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $activeMedication = Medication::factory()->for($patient)->create(['name' => 'Actief']);
    MedicationSchedule::factory()->forMedication($activeMedication)->create([
        'end_date' => '2026-12-31',
    ]);

    $endedMedication = Medication::factory()->for($patient)->create(['name' => 'Afgelopen']);
    MedicationSchedule::factory()->forMedication($endedMedication)->create([
        'end_date' => '2026-05-01',
    ]);

    $removedMedication = Medication::factory()->for($patient)->create(['name' => 'Verwijderd']);
    MedicationSchedule::factory()->forMedication($removedMedication)->create([
        'end_date' => null,
    ]);
    $removedMedication->delete();

    $response = $this->actingAs($user)->get(route('patient.medications'));

    $response->assertOk();
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->where('active_medications.meta.total', 1)
        ->where('active_medications.data.0.name', 'Actief')
        ->missing('previously_used_medications')));
});

test('legacy pharmacist overview url redirects to share with pharmacist page', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->get('/patient/medications/pharmacist-overview');

    $response->assertRedirect(route('patient.medications.share-with-pharmacist'));
});

test('patients can view active medications on the pharmacist overview page', function () {
    CarbonImmutable::setTestNow('2026-05-19 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $activeMedication = Medication::factory()->for($patient)->create(['name' => 'Metformine']);
    MedicationSchedule::factory()->forMedication($activeMedication)->create([
        'end_date' => '2026-12-31',
    ]);

    $response = $this->actingAs($user)->get(route('patient.medications.share-with-pharmacist'));

    $response->assertOk();
    assertInertiaRootComponent($response, 'Patient/Medications/ShareWithPharmacist');
    $response->assertInertia(loadAllDeferredInertiaProps(fn ($page) => $page
        ->where('medication_names', ['Metformine'])));
});

test('patients without active medications are redirected from the pharmacist overview page', function () {
    CarbonImmutable::setTestNow('2026-05-19 10:00:00');

    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->get(route('patient.medications.share-with-pharmacist'));

    $response->assertRedirect(route('patient.medications'));
});

test('patients cannot update medications that are no longer active on the list', function () {
    CarbonImmutable::setTestNow('2026-05-19 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $endedMedication = Medication::factory()->for($patient)->create(['name' => 'Afgelopen']);
    MedicationSchedule::factory()->forMedication($endedMedication)->create([
        'end_date' => '2026-05-01',
    ]);

    $response = $this->actingAs($user)->put(route('patient.medications.update', $endedMedication), [
        'name' => 'Nieuwe naam',
    ]);

    $response->assertForbidden();
});

test('destroying a medication soft deletes it and related schedule and stock', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();
    $schedule = MedicationSchedule::factory()->forMedication($medication)->create();
    $stock = MedicationStock::factory()->for($medication)->create();

    $response = $this->actingAs($user)->delete(route('patient.medications.destroy', $medication));

    $response->assertRedirect(route('patient.medications'));

    expect(Medication::query()->find($medication->id))->toBeNull();
    expect(Medication::withTrashed()->find($medication->id)?->trashed())->toBeTrue();
    expect(MedicationSchedule::query()->find($schedule->id))->toBeNull();
    expect(MedicationSchedule::withTrashed()->find($schedule->id)?->trashed())->toBeTrue();
    expect(MedicationStock::query()->find($stock->id))->toBeNull();
    expect(MedicationStock::withTrashed()->find($stock->id)?->trashed())->toBeTrue();
});

test('patients can create a medication with snooze time per dose slot', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Vitamine D',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        ...validNewMedicationStockPayload(),
        'schedule' => [
            ...validNewMedicationSchedulePayload(),
            'times_per_day' => '2',
            'dose_time' => '09:00, 12:00',
            'snooze_time' => '60, 30',
        ],
    ]);

    $response->assertRedirect(route('patient.medications'));

    $schedule = MedicationSchedule::query()
        ->whereHas('medication', fn ($query) => $query->where('patient_id', $patient->id))
        ->first();

    expect($schedule)->not->toBeNull();
    expect($schedule->dose_time)->toBe('09:00, 12:00')
        ->and($schedule->snooze_time)->toBe('60, 30');
});

test('patients can update a medication schedule snooze time per dose slot', function () {
    CarbonImmutable::setTestNow('2026-05-19 10:00:00');

    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $medication = Medication::factory()->for($patient)->create();
    $schedule = MedicationSchedule::factory()->forMedication($medication)->create([
        'times_per_day' => '2',
        'dose_time' => '09:00, 12:00',
        'start_date' => '2026-05-01',
        'end_date' => '2026-12-31',
    ]);

    $intakeFrequency = $schedule->intake_frequency;

    $response = $this->actingAs($user)->put(route('patient.medications.update', $medication), [
        'name' => $medication->name,
        'dose' => $medication->dose,
        'dose_unit' => $medication->dose_unit->value,
        'type_medication' => $medication->type_medication->value,
        'schedule' => [
            'meal_timing' => $schedule->meal_timing->value,
            'intake_frequency' => $intakeFrequency instanceof BackedEnum
                ? $intakeFrequency->value
                : $intakeFrequency,
            'times_per_day' => '2',
            'dose_time' => '09:00, 12:00',
            'snooze_time' => '90, 45',
            'start_date' => $schedule->start_date?->format('Y-m-d'),
            'end_date' => $schedule->end_date?->format('Y-m-d'),
        ],
    ]);

    $response->assertRedirect(route('patient.medications'));

    $schedule->refresh();

    expect($schedule->dose_time)->toBe('09:00, 12:00')
        ->and($schedule->snooze_time)->toBe('90, 45');
});

test('patients can create a medication and name is encrypted at rest', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Ibuprofen',
        'dose' => '400',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        ...validNewMedicationStockPayload(),
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertRedirect(route('patient.medications'));

    $medication = Medication::query()->where('patient_id', $patient->id)->first();
    expect($medication)->not->toBeNull();
    expect($medication->name)->toBe('Ibuprofen');
    expect($medication->dose)->toBe('400');
    expect($medication->dose_unit)->toBe(MedicationDoseUnit::PIECE);

    $schedule = MedicationSchedule::query()->where('medication_id', $medication->id)->first();
    expect($schedule)->not->toBeNull();
    expect($schedule->meal_timing)->toBe(MedicationMealTiming::WITH_FOOD);
    expect($schedule->intake_frequency)->toBe(MedicationIntakeFrequency::DAILY);
    expect($schedule->intake_weekdays)->toBeNull();
    expect($schedule->times_per_day)->toBe('1');
    expect($schedule->dose_quantity)->toBe('400');
    expect($schedule->dose_time)->toBe('09:00');
    expect($schedule->snooze_time)->toBe('30');
    expect($schedule->start_date?->format('Y-m-d'))->toBe('2026-05-01');
    expect($schedule->end_date?->format('Y-m-d'))->toBe('2026-05-07');

    $stock = MedicationStock::query()->where('medication_id', $medication->id)->first();
    expect($stock)->not->toBeNull();
    expect($stock->current_stock)->toBe('20');
    expect((int) $medication->stock_pieces_per_package)->toBe(10);

    $raw = DB::table('medications')->where('id', $medication->id)->first();
    expect($raw->name)->not->toBe('Ibuprofen');
    expect($raw->dose)->not->toBe('400');
    expect($raw->stock_pieces_per_package)->not->toBe('10');
});

test('medication prescriptions are stored separately from medications', function () {
    $medication = Medication::factory()->create();

    MedicationPrescription::factory()->forMedication($medication)->create([
        'prescription_expiry_date' => '2026-12-31',
    ]);

    $medication->load('prescription');

    expect($medication->prescription?->prescription_expiry_date?->format('Y-m-d'))->toBe('2026-12-31');

    $rawMedicationColumns = array_keys((array) DB::table('medications')->where('id', $medication->id)->first());

    expect($rawMedicationColumns)->not->toContain('prescription_expiry_date');
});

test('patients can create a medication with an optional trimmed note', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Aspirine',
        'dose' => '100',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
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

test('patients cannot store a medication without a schedule start date', function () {
    $user = User::factory()->patient()->create();

    $schedule = validNewMedicationSchedulePayload();
    unset($schedule['start_date']);

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Zonder startdatum',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        ...validNewMedicationStockPayload(),
        'schedule' => $schedule,
    ]);

    $response->assertSessionHasErrors(['schedule.start_date']);
});

test('patients can create a medication with optional strength separate from intake dose', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Paracetamol',
        'dose' => '1',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'strength' => '1000 mg',
        'type_medication' => MedicationType::PILL->value,
        ...validNewMedicationStockPayload(),
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertRedirect(route('patient.medications'));

    $medication = Medication::query()->where('patient_id', $patient->id)->first();
    expect($medication)->not->toBeNull();
    expect($medication->dose)->toBe('1');
    expect($medication->dose_unit)->toBe(MedicationDoseUnit::PIECE);
    expect($medication->strength)->toBe('1000 mg');

    $schedule = MedicationSchedule::query()->where('medication_id', $medication->id)->first();
    expect($schedule)->not->toBeNull();
    expect($schedule->dose_quantity)->toBe('1');

    $raw = DB::table('medications')->where('id', $medication->id)->first();
    expect((string) $raw->strength)->not->toBe('1000 mg');
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
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertSessionHasErrors(['current_stock', 'stock_pieces_per_package']);
});

test('patients can create a medication when a family is linked', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $family = Family::factory()->create();
    $patient->families()->syncWithoutDetaching([$family->id]);

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Vitamine C',
        'dose' => '1000',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        ...validNewMedicationStockPayload(),
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertRedirect(route('patient.medications'));

    $medication = Medication::query()->where('patient_id', $patient->id)->first();
    expect($medication)->not->toBeNull();
    expect($medication->name)->toBe('Vitamine C');

    $schedule = MedicationSchedule::query()->where('medication_id', $medication->id)->first();
    expect($schedule)->not->toBeNull();
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
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
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
        ...validNewMedicationStockPayload(),
        'schedule' => $schedule,
    ]);

    $response->assertRedirect(route('patient.medications'));

    $medication = Medication::query()->where('patient_id', $patient->id)->first();
    expect($medication)->not->toBeNull();

    $saved = MedicationSchedule::query()->where('medication_id', $medication->id)->first();
    expect($saved)->not->toBeNull();
    $saved->load('weekdays');
    expect($saved->intake_frequency)->toBe(MedicationIntakeFrequency::WEEKDAYS);
    expect($saved->intake_weekdays)->toBe([1, 2]);
    expect(MedicationScheduleWeekday::query()->where('medication_schedule_id', $saved->id)->count())->toBe(2);
});

test('doctors cannot create patient medications', function () {
    $user = User::factory()->doctor()->create();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Test',
        'type_medication' => MedicationType::PILL->value,
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
    expect($medications)->toHaveCount(5);

    $levothyroxine = $medications->firstWhere('name', 'Levothyroxine');
    expect($levothyroxine)->not->toBeNull();
    expect($levothyroxine->dose)->toBe('1');
    expect($levothyroxine->dose_unit)->toBe(MedicationDoseUnit::PIECE);
    expect($levothyroxine->type_medication)->toBe(MedicationType::PILL);

    $schedule = $levothyroxine->schedules()->first();
    expect($schedule)->not->toBeNull();
    expect($schedule->times_per_day)->toBe('1');
    expect($schedule->dose_time)->toBe('06:45');
    expect($schedule->meal_timing)->toBe(MedicationMealTiming::BEFORE_FOOD);
    expect($schedule->intake_frequency)->toBe(MedicationIntakeFrequency::DAILY);
    $rawSchedule = DB::table('medication_schedules')->where('id', $schedule->id)->first();
    expect($rawSchedule->times_per_day)->not->toBe('1');
    expect($rawSchedule->dose_time)->not->toBe('06:45');

    $stock = $levothyroxine->stocks()->first();
    expect($stock)->not->toBeNull();
    expect($stock->current_stock)->toBe('84 stuks');
    $rawStock = DB::table('medication_stocks')->where('id', $stock->id)->first();
    expect($rawStock->current_stock)->not->toBe('84 stuks');

    $levoPrescription = $levothyroxine->prescriptions()->first();
    expect($levoPrescription)->not->toBeNull();
    expect($levoPrescription->pickup_status)->toBe(MedicationPrescriptionPickupStatus::PICKED_UP);
    $metformin = $medications->firstWhere('name', 'Metformine');
    expect($metformin)->not->toBeNull();
    expect($metformin->dose)->toBe('1');
    expect($metformin->dose_unit)->toBe(MedicationDoseUnit::PIECE);
    expect($metformin->schedules()->first()?->times_per_day)->toBe('2');
    expect($metformin->schedules()->first()?->dose_time)->toBe('08:00, 19:00');
    expect($metformin->schedules()->first()?->meal_timing)->toBe(MedicationMealTiming::AFTER_FOOD);
    expect($metformin->stocks()->first()?->current_stock)->toBe('11 stuks');

    $metforminPrescription = $metformin->prescriptions()->first();
    expect($metforminPrescription)->not->toBeNull();
    expect($metforminPrescription->pickup_status)->toBe(MedicationPrescriptionPickupStatus::PENDING);
    expect($metforminPrescription->is_last_in_batch)->toBeTrue();

    $methotrexaat = $medications->firstWhere('name', 'Methotrexaat injectie');
    expect($methotrexaat)->not->toBeNull();
    expect($methotrexaat->dose_unit)->toBe(MedicationDoseUnit::MILLILITER);
    expect($methotrexaat->type_medication)->toBe(MedicationType::INJECTION);
    $methotrexaatSchedule = $methotrexaat->schedules()->first()?->load('weekdays');
    expect($methotrexaatSchedule?->meal_timing)->toBe(MedicationMealTiming::UNRELATED);
    expect($methotrexaatSchedule?->intake_frequency)->toBe(MedicationIntakeFrequency::WEEKDAYS);
    expect($methotrexaatSchedule?->intake_weekdays)->toBe([4]);
    expect($methotrexaat->stocks()->first()?->current_stock)->toBe('12 ml');

    $magnesium = $medications->firstWhere('name', 'Magnesiumcitraat');
    expect($magnesium)->not->toBeNull();
    expect($magnesium->dose_unit)->toBe(MedicationDoseUnit::PIECE);
    expect($magnesium->type_medication)->toBe(MedicationType::SACHETS);
    expect($magnesium->schedules()->first()?->dose_time)->toBe('18:30');
    expect($magnesium->schedules()->first()?->intake_frequency)->toBe(MedicationIntakeFrequency::everyNDaysValue(2));
    expect($magnesium->stocks()->first()?->current_stock)->toBe('6 stuks');
    expect($magnesium->prescriptions()->count())->toBe(0);

    $oogdruppels = $medications->firstWhere('name', 'Hyaluronzuur oogdruppels');
    expect($oogdruppels)->not->toBeNull();
    expect($oogdruppels->dose_unit)->toBe(MedicationDoseUnit::DROP);
    expect($oogdruppels->type_medication)->toBe(MedicationType::LIQUID);
    expect($oogdruppels->strength)->toBe('1 mg per druppel');
    expect($oogdruppels->schedules()->first()?->times_per_day)->toBe('3');
    expect($oogdruppels->schedules()->first()?->dose_time)->toBe('08:00, 14:00, 20:00');
    expect($oogdruppels->stocks()->first()?->current_stock)->toBe('270 druppels');
});

test('patients cannot store a medication with the unit dose unit', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Insuline',
        'dose' => '10',
        'dose_unit' => MedicationDoseUnit::UNIT->value,
        'type_medication' => MedicationType::INJECTION->value,
        ...validNewMedicationStockPayload(),
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertSessionHasErrors('dose_unit');
});

test('patients cannot store medications with the drop dose unit on create', function () {
    $user = User::factory()->patient()->create();

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Vitamine D',
        'dose' => '2',
        'dose_unit' => MedicationDoseUnit::DROP->value,
        'type_medication' => MedicationType::LIQUID->value,
        'strength' => '10 mg per druppel',
        ...validNewMedicationStockPayload(),
        'schedule' => validNewMedicationSchedulePayload(),
    ]);

    $response->assertSessionHasErrors('dose_unit');
});

test('patients can create a medication with an ongoing schedule without end date', function () {
    $user = User::factory()->patient()->create();
    $patient = $user->patient;
    expect($patient)->not->toBeNull();

    $schedule = validNewMedicationSchedulePayload();
    $schedule['end_date'] = null;

    $response = $this->actingAs($user)->post(route('patient.medications.store'), [
        'name' => 'Levothyroxine',
        'dose' => '75',
        'dose_unit' => MedicationDoseUnit::PIECE->value,
        'type_medication' => MedicationType::PILL->value,
        ...validNewMedicationStockPayload(),
        'schedule' => $schedule,
    ]);

    $response->assertRedirect(route('patient.medications'));

    $medication = Medication::query()->where('patient_id', $patient->id)->first();
    expect($medication)->not->toBeNull();

    $saved = MedicationSchedule::query()->where('medication_id', $medication->id)->first();
    expect($saved)->not->toBeNull();
    expect($saved->end_date)->toBeNull();
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
