<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\MedicationStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicationStock>
 */
class MedicationStockFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (MedicationStock $stock): void {
            if ($stock->medication_id === null) {
                return;
            }

            $medication = Medication::query()->find($stock->medication_id);

            if ($medication === null) {
                return;
            }

            $stock->patient_id = $medication->patient_id;
            $stock->family_id = $medication->family_id;
        });
    }

    public function definition(): array
    {
        return [
            'medication_id' => Medication::factory(),
            'current_stock' => (string) fake()->numberBetween(0, 100),
            'low_stock' => (string) fake()->numberBetween(1, 10),
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
