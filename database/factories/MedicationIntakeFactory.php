<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicationIntake>
 */
class MedicationIntakeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'medication_id' => Medication::factory(),
            'medication_schedule_id' => MedicationSchedule::factory(),
            'intake_date' => now()->toDateString(),
            'dose_time' => '08:00',
            'taken_at' => now(),
        ];
    }

    public function forSchedule(MedicationSchedule $schedule): static
    {
        return $this->state(fn (array $attributes): array => [
            'patient_id' => $schedule->patient_id,
            'medication_id' => $schedule->medication_id,
            'medication_schedule_id' => $schedule->id,
        ]);
    }
}
