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
    public function definition(): array
    {
        return [
            'medication_id' => Medication::factory(),
            'current_stock' => (string) fake()->numberBetween(0, 100),
        ];
    }

    public function forMedication(Medication $medication): static
    {
        return $this->state(fn (array $attributes): array => [
            'medication_id' => $medication->id,
        ]);
    }
}
