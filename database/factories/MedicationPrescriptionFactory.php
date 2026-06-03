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
    public function configure(): static
    {
        return $this->afterMaking(function (MedicationPrescription $prescription): void {
            if ($prescription->medication_id === null) {
                return;
            }

            $medication = Medication::query()->find($prescription->medication_id);

            if ($medication === null) {
                return;
            }

            $prescription->patient_id = $medication->patient_id;
            $prescription->family_id = $medication->family_id;
        });
    }

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
            'patient_id' => $medication->patient_id,
            'family_id' => $medication->family_id,
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
