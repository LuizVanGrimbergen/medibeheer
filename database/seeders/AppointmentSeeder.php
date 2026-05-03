<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        Patient::query()->each(function (Patient $patient): void {
            Appointment::factory()
                ->count(fake()->numberBetween(1, 4))
                ->for($patient)
                ->create();
        });
    }
}
