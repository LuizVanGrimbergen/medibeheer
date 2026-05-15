<?php

namespace Database\Seeders;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationType;
use App\Models\Family;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MedicationSeeder extends Seeder
{
    public function run(?Patient $patient = null, ?Family $family = null): void
    {
        if ($patient === null) {
            if ($this->command !== null) {
                $this->command->warn('MedicationSeeder skipped: no patient provided.');
            }

            return;
        }

        $familyId = $family?->id;
        $today = Carbon::now()->startOfDay();

        $demos = [
            [
                'name' => 'Paracetamol',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::PIECE,
                'type_medication' => MedicationType::PILL,
                'strength' => '500 mg',
                'note' => 'Maximaal 6 tabletten in 24 uur. Bij aanhoudende koorts of pijn: huisarts.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::UNRELATED,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '3',
                    'dose_quantity' => '1',
                    'dose_time' => '08:00, 14:00, 21:00',
                    'start_date' => $today->copy()->subWeek()->toDateString(),
                    'end_date' => $today->copy()->addMonths(2)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '72',
                    'low_stock' => '18',
                ],
            ],
            [
                'name' => 'Metformine',
                'dose' => '500',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'strength' => '500 mg',
                'note' => 'Tijdens of direct na de maaltijd innemen om maag-darmklachten te beperken.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::WITH_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '2',
                    'dose_quantity' => '500',
                    'dose_time' => '08:30, 18:30',
                    'start_date' => $today->copy()->subMonths(3)->toDateString(),
                    'end_date' => $today->copy()->addMonths(6)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '38',
                    'low_stock' => '28',
                ],
            ],
            [
                'name' => 'Atorvastatine',
                'dose' => '40',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'strength' => '40 mg',
                'note' => 'Voor het slapen gaan innemen, tenzij de arts anders voorschrijft.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::AFTER_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '40',
                    'dose_time' => '21:00',
                    'start_date' => $today->copy()->subMonths(2)->toDateString(),
                    'end_date' => $today->copy()->addYear()->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '84',
                    'low_stock' => '14',
                ],
            ],
            [
                'name' => 'Omeprazol',
                'dose' => '20',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'strength' => '20 mg',
                'note' => 'Capsule heel doorslikken; niet kauwen of openmaken.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::BEFORE_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '20',
                    'dose_time' => '07:15',
                    'start_date' => $today->copy()->subWeeks(6)->toDateString(),
                    'end_date' => $today->copy()->addMonths(4)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '10',
                    'low_stock' => '14',
                ],
            ],
            [
                'name' => 'Bisoprololfumaraat',
                'dose' => '2.5',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'strength' => '2,5 mg',
                'note' => 'Niet zonder overleg stoppen; bij duizeligheid of kortademigheid: arts bellen.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::UNRELATED,
                    'intake_frequency' => MedicationIntakeFrequency::WEEKDAYS,
                    'intake_weekdays' => [1, 2, 3, 4, 5],
                    'times_per_day' => '1',
                    'dose_quantity' => '2.5',
                    'dose_time' => '08:00',
                    'start_date' => $today->copy()->subMonth()->toDateString(),
                    'end_date' => $today->copy()->addMonths(5)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '24',
                    'low_stock' => '18',
                ],
            ],
            [
                'name' => 'Levothyroxine',
                'dose' => '75',
                'dose_unit' => MedicationDoseUnit::OTHER,
                'type_medication' => MedicationType::PILL,
                'strength' => '75 microgram',
                'note' => 'Op nuchtere maag met water; minstens een half uur voor ontbijt geen calcium- of ijzerpreparaten.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::BEFORE_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '75',
                    'dose_time' => '06:45',
                    'start_date' => $today->copy()->subYears(1)->toDateString(),
                    'end_date' => $today->copy()->addYear()->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '200',
                    'low_stock' => '30',
                ],
            ],
            [
                'name' => 'Salbutamol',
                'dose' => '2',
                'dose_unit' => MedicationDoseUnit::UNIT,
                'type_medication' => MedicationType::OTHER,
                'strength' => '100 microgram per puff (aerosol)',
                'note' => 'Bij benauwdheid; bij geen verbetering of verergering: 112 of huisarts.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::UNRELATED,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '2',
                    'dose_quantity' => '2',
                    'dose_time' => '09:00, 20:00',
                    'start_date' => $today->copy()->subWeeks(3)->toDateString(),
                    'end_date' => $today->copy()->addMonths(3)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '40',
                    'low_stock' => '60',
                ],
            ],
            [
                'name' => 'Cholecalciferol',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::PIECE,
                'type_medication' => MedicationType::PILL,
                'strength' => '25 microgram (1000 IE)',
                'note' => 'Eén tablet per week, bij voorkeur dezelfde dag en bij een maaltijd.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::WITH_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::everyNDaysValue(7),
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '1',
                    'dose_time' => '10:30',
                    'start_date' => $today->copy()->subMonths(4)->toDateString(),
                    'end_date' => $today->copy()->addMonths(8)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '24',
                    'low_stock' => '6',
                ],
            ],
            [
                'name' => 'Insuline glargine',
                'dose' => '24',
                'dose_unit' => MedicationDoseUnit::INJECTION,
                'type_medication' => MedicationType::INJECTION,
                'strength' => '100 E/ml',
                'note' => 'Basale insuline op vast tijdstip; roteer injectieplaatsen (buik/bovenbeen).',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::UNRELATED,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '24',
                    'dose_time' => '22:00',
                    'start_date' => $today->copy()->subMonths(6)->toDateString(),
                    'end_date' => $today->copy()->addMonths(6)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '36',
                    'low_stock' => '72',
                ],
            ],
            [
                'name' => 'Hydrocortisoncrème',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::OTHER,
                'type_medication' => MedicationType::CREAM,
                'strength' => '1 %',
                'note' => 'Dun laagje op de aangedane huid; niet langdurig op gezicht of grote oppervlakken zonder overleg.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::UNRELATED,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '2',
                    'dose_quantity' => '1',
                    'dose_time' => '08:00, 19:00',
                    'start_date' => $today->copy()->subDays(10)->toDateString(),
                    'end_date' => $today->copy()->addWeeks(2)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '3',
                    'low_stock' => '2',
                ],
            ],
            [
                'name' => 'Lactulose',
                'dose' => '15',
                'dose_unit' => MedicationDoseUnit::MILLILITER,
                'type_medication' => MedicationType::LIQUID,
                'strength' => '670 mg/ml',
                'note' => 'Fles goed schudden; eventueel verdund met water of sap.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::AFTER_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '2',
                    'dose_quantity' => '15',
                    'dose_time' => '09:00, 21:00',
                    'start_date' => $today->copy()->subWeeks(2)->toDateString(),
                    'end_date' => $today->copy()->addMonths(1)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '750',
                    'low_stock' => '200',
                ],
            ],
            [
                'name' => 'Macrogol met elektrolyten',
                'dose' => '13.5',
                'dose_unit' => MedicationDoseUnit::GRAM,
                'type_medication' => MedicationType::SACHETS,
                'strength' => '13,5 g per zakje',
                'note' => 'Zakje oplossen in een half glas water; meteen opdrinken.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::UNRELATED,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '13.5',
                    'dose_time' => '07:45',
                    'start_date' => $today->copy()->subDays(5)->toDateString(),
                    'end_date' => $today->copy()->addWeeks(3)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '6',
                    'low_stock' => '10',
                ],
            ],
            [
                'name' => 'Travoprost',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::DROP,
                'type_medication' => MedicationType::LIQUID,
                'strength' => '40 microgram/ml',
                'note' => 'Eén druppel in het aangedane oog; daarna een minuut zachtjes drukken op de traanbuis.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::UNRELATED,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '1',
                    'dose_time' => '22:30',
                    'start_date' => $today->copy()->subMonths(2)->toDateString(),
                    'end_date' => $today->copy()->addMonths(10)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '11',
                    'low_stock' => '8',
                ],
            ],
            [
                'name' => 'Prednisolon',
                'dose' => '5',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'strength' => '5 mg',
                'note' => 'Om de dag volgens schema van de arts; niet zelf stoppen of dosering wijzigen.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::WITH_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::everyNDaysValue(2),
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '5',
                    'dose_time' => '08:30',
                    'start_date' => $today->copy()->subWeeks(8)->toDateString(),
                    'end_date' => $today->copy()->addMonths(2)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '15',
                    'low_stock' => '6',
                ],
            ],
            [
                'name' => 'Magnesiumcitraat',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::SACHET,
                'type_medication' => MedicationType::SACHETS,
                'strength' => '375 mg per zakje',
                'note' => 'Zakje oplossen in water; innemen voor het slapen gaan.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::AFTER_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '1',
                    'dose_time' => '21:45',
                    'start_date' => $today->copy()->subDays(14)->toDateString(),
                    'end_date' => $today->copy()->addMonths(3)->toDateString(),
                ],
                'stock' => [
                    'current_stock' => '28',
                    'low_stock' => '8',
                ],
            ],
        ];

        foreach ($demos as $demo) {
            $schedule = $demo['schedule'];
            $stock = $demo['stock'];
            unset($demo['schedule'], $demo['stock']);

            $medication = $patient->medications()->create(array_merge(
                $demo,
                $familyId !== null ? ['family_id' => $familyId] : [],
            ));

            $medication->schedules()->create(array_merge($schedule, [
                'patient_id' => $medication->patient_id,
                'family_id' => $medication->family_id,
            ]));

            $medication->stocks()->create(array_merge($stock, [
                'patient_id' => $medication->patient_id,
                'family_id' => $medication->family_id,
            ]));
        }

        if ($this->command !== null) {
            $this->command->info('MedicationSeeder finished: demo medications created.');
        }
    }
}
