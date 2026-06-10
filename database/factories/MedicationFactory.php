<?php

namespace Database\Factories;

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
            'name' => fake()->words(3, true),
            'dose' => fake()->randomElement(['500 mg', '1 tablet', '10 ml', '2 druppels']),
            'dose_unit' => fake()->randomElement(MedicationDoseUnit::cases()),
            'type_medication' => fake()->randomElement(MedicationType::cases()),
            'strength' => fake()->optional(0.35)->randomElement([
                '500 mg per tablet',
                '20 mg per ml',
                '400 IE',
            ]),
            'note' => fake()->optional(0.25)->realTextBetween(20, 120),
        ];
    }
}
