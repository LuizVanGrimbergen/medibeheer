<?php

namespace Database\Factories;

use App\Enums\MedicationPrescriptionPickupStatus;
use App\Models\Medication;
use App\Models\MedicationPrescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicationPrescription>
 */
class MedicationPrescriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'medication_id' => Medication::factory(),
            'prescription_expiry_date' => fake()->optional(0.7)->dateTimeBetween('+1 month', '+2 years'),
            'pickup_status' => MedicationPrescriptionPickupStatus::PENDING,
        ];
    }

    public function forMedication(Medication $medication): static
    {
        return $this->state(fn (array $attributes): array => [
            'medication_id' => $medication->id,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'completed_at' => now(),
        ]);
    }

    public function pickedUp(): static
    {
        return $this->state(fn (array $attributes): array => [
            'pickup_status' => MedicationPrescriptionPickupStatus::PICKED_UP,
        ]);
    }

    public function lastInBatch(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_last_in_batch' => true,
        ]);
    }
}
