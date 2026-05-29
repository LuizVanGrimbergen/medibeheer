<?php

namespace Database\Seeders;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationType;
use App\Models\Family;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\Patient;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use App\Support\MedicationScheduleDoseTimeFields;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class MedicationSeeder extends Seeder
{
    private const int INTAKE_HISTORY_DAYS = 14;

    private const int MISSED_HISTORICAL_DOSE_PERCENT = 8;

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

        $createdSchedules = [];

        foreach ($this->demoMedications($today) as $demo) {
            $scheduleRaw = $demo['schedule'];
            $stock = $demo['stock'];
            unset($demo['schedule'], $demo['stock']);

            $medication = $patient->medications()->create(array_merge(
                $demo,
                $familyId !== null ? ['family_id' => $familyId] : [],
            ));

            $normalized = MedicationScheduleIntakeWeekdays::normalizeNestedSchedule($scheduleRaw);
            $normalized = MedicationScheduleDoseTimeFields::normalizeNestedSchedule($normalized);
            $weekdayList = $normalized['intake_weekdays'];
            $scheduleAttrs = Arr::except($normalized, ['intake_weekdays']);

            $schedule = $medication->schedules()->create(array_merge($scheduleAttrs, [
                'patient_id' => $medication->patient_id,
                'family_id' => $medication->family_id,
            ]));
            $schedule->syncIntakeWeekdays($weekdayList);

            $medication->stocks()->create(array_merge($stock, [
                'patient_id' => $medication->patient_id,
                'family_id' => $medication->family_id,
            ]));

            $createdSchedules[] = $schedule->load('weekdays');
        }

        $this->seedIntakeHistory($patient, $createdSchedules);

        if ($this->command !== null) {
            $this->command->info('MedicationSeeder finished: realistic demo medications created.');
        }
    }

    private function demoMedications(Carbon $today): array
    {
        return [
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
                    'snooze_time' => '30',
                    'start_date' => $today->copy()->subYears(2)->toDateString(),
                    'end_date' => $today->copy()->addYear()->toDateString(),
                ],
                'stock' => ['current_stock' => '2250'],
            ],
            [
                'name' => 'Metformine',
                'dose' => '500',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'strength' => '500 mg',
                'note' => 'Tijdens of direct na de lunch innemen om maag-darmklachten te beperken.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::WITH_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '500',
                    'dose_time' => '12:30',
                    'snooze_time' => '30',
                    'start_date' => $today->copy()->subYear()->toDateString(),
                    'end_date' => $today->copy()->addMonths(6)->toDateString(),
                ],
                'stock' => ['current_stock' => '5000'],
            ],
            [
                'name' => 'Magnesiumcitraat',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::SACHET,
                'type_medication' => MedicationType::SACHETS,
                'strength' => '375 mg per zakje',
                'note' => 'Zakje oplossen in een glas water; bij het avondeten innemen.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::WITH_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '1',
                    'dose_time' => '18:30',
                    'snooze_time' => '30',
                    'start_date' => $today->copy()->subMonths(2)->toDateString(),
                    'end_date' => $today->copy()->addMonths(3)->toDateString(),
                ],
                'stock' => ['current_stock' => '5'],
            ],
            [
                'name' => 'Atorvastatine',
                'dose' => '40',
                'dose_unit' => MedicationDoseUnit::MILLIGRAM,
                'type_medication' => MedicationType::PILL,
                'strength' => '40 mg',
                'note' => '’s Avonds voor het slapengaan innemen. Meld onverklaarde spierpijn of donkere urine aan huisarts.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::AFTER_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '40',
                    'dose_time' => '22:00',
                    'snooze_time' => '30',
                    'start_date' => $today->copy()->subYear()->toDateString(),
                    'end_date' => $today->copy()->addYear()->toDateString(),
                ],
                'stock' => ['current_stock' => '440'],
            ],
        ];
    }

    /**
     * @param  array<int, MedicationSchedule>  $schedules
     */
    private function seedIntakeHistory(Patient $patient, array $schedules): void
    {
        $occursOnDate = new MedicationScheduleOccursOnDate;
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();

        foreach ($schedules as $schedule) {
            $doseTimes = $occursOnDate->sortedDoseTimes($schedule);

            for ($dayOffset = self::INTAKE_HISTORY_DAYS; $dayOffset >= 0; $dayOffset--) {
                $intakeDate = $today->copy()->subDays($dayOffset);

                if (! $occursOnDate->isIntakeDueOn($schedule, $intakeDate)) {
                    continue;
                }

                foreach ($doseTimes as $doseTime) {
                    $this->seedIntakeFor($patient, $schedule, $intakeDate, $doseTime, $now, $dayOffset);
                }
            }
        }
    }

    private function seedIntakeFor(
        Patient $patient,
        MedicationSchedule $schedule,
        Carbon $intakeDate,
        string $doseTime,
        Carbon $now,
        int $dayOffset,
    ): void {
        $trimmedDoseTime = trim($doseTime);

        if ($trimmedDoseTime === '') {
            return;
        }

        $doseMoment = $this->doseMoment($intakeDate, $trimmedDoseTime);

        if ($doseMoment === null) {
            return;
        }

        if ($dayOffset === 0 && $doseMoment->gt($now)) {
            return;
        }

        if ($dayOffset > 0 && random_int(1, 100) <= self::MISSED_HISTORICAL_DOSE_PERCENT) {
            return;
        }

        $intake = MedicationIntake::firstOrNewForScheduleDateAndDoseTime(
            $schedule->id,
            $intakeDate->toDateString(),
            $trimmedDoseTime,
        );

        $intake->fill([
            'patient_id' => $patient->id,
            'medication_id' => $schedule->medication_id,
            'taken_at' => $doseMoment->copy()->addMinutes(random_int(-10, 25)),
        ]);
        $intake->save();
    }

    private function doseMoment(Carbon $date, string $doseTime): ?Carbon
    {
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $doseTime, $matches) !== 1) {
            return null;
        }

        $hours = (int) $matches[1];
        $minutes = (int) $matches[2];

        if ($hours > 23 || $minutes > 59) {
            return null;
        }

        return $date->copy()->setTime($hours, $minutes, 0);
    }
}
