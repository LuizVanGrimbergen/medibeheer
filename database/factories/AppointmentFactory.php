<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'family_id' => null,
            'doctor_type' => fake()->randomElement(DoctorType::cases()),
            'provider_name' => fake()->company(),
            'street' => fake()->streetName(),
            'house_number' => (string) fake()->buildingNumber(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'starts_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'needs_transport' => false,
            'notes' => fake()->optional(0.4)->paragraph(),
            'doctor_visit_summary' => null,
            'cancellation_reason' => null,
            'status' => fake()->randomElement(AppointmentStatus::cases()),
        ];
    }
}
