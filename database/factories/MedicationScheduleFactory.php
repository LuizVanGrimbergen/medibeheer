<?php

namespace Database\Factories;

use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicationSchedule>
 */
class MedicationScheduleFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (MedicationSchedule $schedule): void {
            if ($schedule->medication_id === null) {
                return;
            }

            $medication = Medication::query()->find($schedule->medication_id);

            if ($medication === null) {
                return;
            }

            $schedule->patient_id = $medication->patient_id;
            $schedule->family_id = $medication->family_id;
        })->afterCreating(function (MedicationSchedule $schedule): void {
            if ($schedule->intake_frequency !== MedicationIntakeFrequency::WEEKDAYS) {
                return;
            }

            if ($schedule->weekdays()->exists()) {
                return;
            }

            $schedule->syncIntakeWeekdays([1, 3, 5]);
        });
    }

    public function definition(): array
    {
        $intakeFrequency = fake()->randomElement(MedicationIntakeFrequency::allowedValues());

        return [
            'medication_id' => Medication::factory(),
            'meal_timing' => fake()->randomElement(MedicationMealTiming::cases()),
            'intake_frequency' => $intakeFrequency,
            'times_per_day' => (string) fake()->numberBetween(1, 4),
            'dose_quantity' => (string) fake()->randomFloat(1, 0.5, 2),
            'dose_time' => fake()->time('H:i'),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(fake()->numberBetween(0, 30))->toDateString(),
        ];
    }

    public function forMedication(Medication $medication): static
    {
        return $this->state(fn (array $attributes): array => [
            'medication_id' => $medication->id,
            'patient_id' => $medication->patient_id,
            'family_id' => $medication->family_id,
        ]);
    }
}
