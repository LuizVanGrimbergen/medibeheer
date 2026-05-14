<?php

namespace Database\Factories;

use App\Enums\MedicationColor;
use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationType;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'family_id' => null,
            'name' => fake()->words(3, true),
            'dose' => fake()->randomElement(['500 mg', '1 tablet', '10 ml', '2 druppels']),
            'dose_unit' => fake()->randomElement(MedicationDoseUnit::cases()),
            'type_medication' => fake()->randomElement(MedicationType::cases()),
            'color' => fake()->optional(0.5)->randomElement(MedicationColor::cases()),
            'note' => fake()->optional(0.25)->realTextBetween(20, 120),
        ];
    }
}
