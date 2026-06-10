<?php

namespace Database\Seeders;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationPrescriptionPickupStatus;
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

    public function run(?Patient $patient = null, ?Family $family = null, bool $presentationProfile = false): void
    {
        if ($patient === null) {
            if ($this->command !== null) {
                $this->command->warn('MedicationSeeder skipped: no patient provided.');
            }

            return;
        }

        $today = Carbon::now()->startOfDay();

        $createdSchedules = [];

        foreach ($this->demoMedications($today, $presentationProfile) as $demo) {
            $scheduleRaw = $demo['schedule'];
            $stock = $demo['stock'];
            $prescription = $demo['prescription'] ?? null;
            unset($demo['schedule'], $demo['stock'], $demo['prescription']);

            $medication = $patient->medications()->create($demo);

            $normalized = MedicationScheduleIntakeWeekdays::normalizeNestedSchedule($scheduleRaw);
            $normalized = MedicationScheduleDoseTimeFields::normalizeNestedSchedule($normalized);
            $weekdayList = $normalized['intake_weekdays'];
            $scheduleAttrs = Arr::except($normalized, ['intake_weekdays']);

            $schedule = $medication->schedules()->create($scheduleAttrs);
            $schedule->syncIntakeWeekdays($weekdayList);

            $medication->stocks()->create($stock);

            if ($prescription !== null) {
                $medication->prescriptions()->create($prescription);
            }

            $createdSchedules[] = $schedule->load('weekdays');
        }

        $this->seedIntakeHistory($patient, $createdSchedules);

        if ($this->command !== null) {
            $message = $presentationProfile
                ? 'MedicationSeeder finished: presentation demo medications created (15:30 and 21:00).'
                : 'MedicationSeeder finished: realistic demo medications created.';

            $this->command->info($message);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function demoMedications(Carbon $today, bool $presentationProfile = false): array
    {
        if ($presentationProfile) {
            return $this->presentationDemoMedications($today);
        }

        return $this->standardDemoMedications($today);
    }

    /**
     * Twee vaste innamemomenten voor live demo's (15:30 en 21:00).
     *
     * @return list<array<string, mixed>>
     */
    private function presentationDemoMedications(Carbon $today): array
    {
        return [
            [
                'name' => 'Metformine',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::PIECE,
                'type_medication' => MedicationType::PILL,
                'stock_pieces_per_package' => 60,
                'strength' => '850 mg',
                'note' => 'Direct na het middageten innemen om maag-darmklachten te beperken.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::AFTER_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '1',
                    'dose_time' => '15:30',
                    'snooze_time' => '30',
                    'start_date' => $today->copy()->subYear()->toDateString(),
                    'end_date' => $today->copy()->addYears(2)->toDateString(),
                ],
                'stock' => ['current_stock' => '11 stuks'],
                'prescription' => [
                    'prescription_expiry_date' => $today->copy()->addDays(6)->toDateString(),
                    'is_last_in_batch' => true,
                    'pickup_status' => MedicationPrescriptionPickupStatus::PENDING,
                    'completed_at' => null,
                ],
            ],
            [
                'name' => 'Magnesiumcitraat',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::PIECE,
                'type_medication' => MedicationType::SACHETS,
                'stock_pieces_per_package' => 20,
                'strength' => '300 mg per zakje',
                'note' => 'Zakje oplossen in een half glas water en bij het avondeten innemen.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::WITH_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '1',
                    'dose_time' => '21:00',
                    'snooze_time' => '30',
                    'start_date' => $today->copy()->subMonths(2)->toDateString(),
                    'end_date' => $today->copy()->addYears(2)->toDateString(),
                ],
                'stock' => ['current_stock' => '6 stuks'],
            ],
        ];
    }

    /**
     * Realistisch medicatieprofiel voor de demo-patiënt. Drie medicaties dekken
     * de meest voorkomende combinaties: medicatietypes (pil, injectie, zakjes),
     * doseereenheden (stuk, milliliter), maaltijdtimings (na/bij eten en los
     * van eten), inname-frequenties (dagelijks, vaste weekdagen en om de
     * zoveel dagen), één t/m drie innamemomenten per dag en voorschriften met
     * uiteenlopende ophaalstatus en vervaldatum.
     */
    private function standardDemoMedications(Carbon $today): array
    {
        return [
            [
                'name' => 'Metformine',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::PIECE,
                'type_medication' => MedicationType::PILL,
                'stock_pieces_per_package' => 60,
                'strength' => '850 mg',
                'note' => 'Direct na het ontbijt en het avondeten innemen om maag-darmklachten te beperken.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::AFTER_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::DAILY,
                    'intake_weekdays' => null,
                    'times_per_day' => '2',
                    'dose_quantity' => '1',
                    'dose_time' => '08:00, 19:00',
                    'snooze_time' => '30, 30',
                    'start_date' => $today->copy()->subYear()->toDateString(),
                    'end_date' => $today->copy()->addYears(2)->toDateString(),
                ],
                'stock' => ['current_stock' => '11 stuks'],
                'prescription' => [
                    'prescription_expiry_date' => $today->copy()->addDays(6)->toDateString(),
                    'is_last_in_batch' => true,
                    'pickup_status' => MedicationPrescriptionPickupStatus::PENDING,
                    'completed_at' => null,
                ],
            ],
            [
                'name' => 'Methotrexaat injectie',
                'dose' => '0,6',
                'dose_unit' => MedicationDoseUnit::MILLILITER,
                'type_medication' => MedicationType::INJECTION,
                'stock_pieces_per_package' => 1,
                'strength' => '25 mg per ml',
                'note' => 'Wekelijkse onderhuidse injectie tegen reuma. Nooit dagelijks gebruiken; de dag erna foliumzuur innemen.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::UNRELATED,
                    'intake_frequency' => MedicationIntakeFrequency::WEEKDAYS,
                    'intake_weekdays' => [4],
                    'times_per_day' => '1',
                    'dose_quantity' => '0,6',
                    'dose_time' => '10:00',
                    'snooze_time' => '30',
                    'start_date' => $today->copy()->subMonths(8)->toDateString(),
                    'end_date' => null,
                ],
                'stock' => ['current_stock' => '12 ml'],
                'prescription' => [
                    'prescription_expiry_date' => $today->copy()->addYear()->toDateString(),
                    'is_last_in_batch' => false,
                    'pickup_status' => MedicationPrescriptionPickupStatus::PICKED_UP,
                    'completed_at' => null,
                ],
            ],
            [
                'name' => 'Magnesiumcitraat',
                'dose' => '1',
                'dose_unit' => MedicationDoseUnit::PIECE,
                'type_medication' => MedicationType::SACHETS,
                'stock_pieces_per_package' => 20,
                'strength' => '300 mg per zakje',
                'note' => 'Zakje oplossen in een half glas water en bij het avondeten innemen; om de dag gebruiken.',
                'schedule' => [
                    'meal_timing' => MedicationMealTiming::WITH_FOOD,
                    'intake_frequency' => MedicationIntakeFrequency::everyNDaysValue(2),
                    'intake_weekdays' => null,
                    'times_per_day' => '1',
                    'dose_quantity' => '1',
                    'dose_time' => '18:30',
                    'snooze_time' => '30',
                    'start_date' => $today->copy()->subMonths(2)->toDateString(),
                    'end_date' => $today->copy()->addYears(2)->toDateString(),
                ],
                'stock' => ['current_stock' => '6 stuks'],
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
