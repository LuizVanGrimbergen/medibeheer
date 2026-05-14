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
                'name' => 'Paracetamol 500 mg',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::PIECE,
                'type_medication' => MedicationType::PILL,
                'note' => 'Maximaal 6 tabletten per 24 uur. Bij aanhoudende koorts contact opnemen met de huisarts.',
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
                    'current_stock' => '48',
                    'low_stock' => '12',
                ],
            ],
            [
                'name' => 'Metformine 500 mg',
                'dose' => '500',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'note' => 'Inname tijdens of vlak na de maaltijd om maag-darmklachten te beperken.',
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
                    'current_stock' => '180',
                    'low_stock' => '28',
                ],
            ],
            [
                'name' => 'Atorvastatine 40 mg',
                'dose' => '40',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'note' => null,
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
                'name' => 'Omeprazol 20 mg',
                'dose' => '20',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'note' => 'Capsule heel doorslikken, niet kauwen of openmaken.',
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
                    'current_stock' => '56',
                    'low_stock' => '10',
                ],
            ],
            [
                'name' => 'Bisoprololfumaraat 2,5 mg',
                'dose' => '2.5',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'note' => 'Niet zomaar stoppen; overleg bij klachten met de behandelend arts.',
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
                    'current_stock' => '100',
                    'low_stock' => '20',
                ],
            ],
            [
                'name' => 'Levothyroxine 75 microgram',
                'dose' => '75',
                'dose_unit' => MedicationDoseUnit::OTHER,
                'type_medication' => MedicationType::PILL,
                'note' => 'Tablet op nuchtere maag met water; minstens een half uur voor het ontbijt geen calcium- of ijzerpreparaten.',
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
                'name' => 'Salbutamol aerosol 100 microgram/dosis',
                'dose' => '2',
                'dose_unit' => MedicationDoseUnit::UNIT,
                'type_medication' => MedicationType::OTHER,
                'note' => 'Bij benauwdheid maximaal 4 pufjes per keer; bij verergering 112 of huisarts.',
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
                    'current_stock' => '2',
                    'low_stock' => '1',
                ],
            ],
            [
                'name' => 'Cholecalciferol (vitamine D3) 25 microgram',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::PIECE,
                'type_medication' => MedicationType::PILL,
                'note' => 'Eén tablet per week, bij voorkeur op dezelfde dag.',
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
                    'current_stock' => '18',
                    'low_stock' => '4',
                ],
            ],
            [
                'name' => 'Insuline glargine 100 E/ml',
                'dose' => '24',
                'dose_unit' => MedicationDoseUnit::INJECTION,
                'type_medication' => MedicationType::INJECTION,
                'note' => 'Injectie op een vaste plek in de buik of bovenbeen; roteer injectieplaatsen.',
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
                    'current_stock' => '5',
                    'low_stock' => '2',
                ],
            ],
            [
                'name' => 'Hydrocortisoncrème 1%',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::OTHER,
                'type_medication' => MedicationType::CREAM,
                'note' => 'Dun laagje op de aangedane huid; niet op gezicht of grote oppervlakken zonder overleg.',
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
                    'current_stock' => '1',
                    'low_stock' => '1',
                ],
            ],
            [
                'name' => 'Lactulose drank 670 mg/ml',
                'dose' => '15',
                'dose_unit' => MedicationDoseUnit::MILLILITER,
                'type_medication' => MedicationType::LIQUID,
                'note' => 'Goed schudden voor gebruik; eventueel verdund met water of sap.',
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
                'name' => 'Macrogol 3350 met elektrolyten 13,5 g',
                'dose' => '13.5',
                'dose_unit' => MedicationDoseUnit::GRAM,
                'type_medication' => MedicationType::SACHETS,
                'note' => 'Zakje oplossen in een half glas water; direct drinken.',
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
                    'current_stock' => '20',
                    'low_stock' => '6',
                ],
            ],
            [
                'name' => 'Travoprost oogdruppels 40 microgram/ml',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::DROP,
                'type_medication' => MedicationType::LIQUID,
                'note' => 'Eén druppel in het aangedane oog; druk een minuut zachtjes op de traanbuis om systemische opname te beperken.',
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
                    'current_stock' => '2',
                    'low_stock' => '1',
                ],
            ],
            [
                'name' => 'Prednisolon 5 mg',
                'dose' => '5',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'note' => 'Om de dag innemen volgens schema van de specialist; niet zelf stoppen.',
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
                'name' => 'Magnesium citraat 375 mg',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::SACHET,
                'type_medication' => MedicationType::SACHETS,
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
                    'current_stock' => '4',
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
