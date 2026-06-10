<?php

namespace Database\Factories;

use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Support\Medications\MedicationScheduleDoseTimes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicationSchedule>
 */
class MedicationScheduleFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (MedicationSchedule $schedule): void {
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
            'snooze_time' => (string) MedicationScheduleDoseTimes::DEFAULT_SNOOZE_MINUTES,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(fake()->numberBetween(0, 30))->toDateString(),
        ];
    }

    public function forMedication(Medication $medication): static
    {
        return $this->state(fn (array $attributes): array => [
            'medication_id' => $medication->id,
        ]);
    }
}
