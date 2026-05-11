<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $patientUser = User::factory()->patient()->create([
            'name' => 'Test Patient',
            'email' => 'test@example.com',
        ]);

        $familyUser = User::factory()->familyMember()->create([
            'name' => 'Test Family',
            'email' => 'family@example.com',
        ]);

        $patient = $patientUser->patient;
        $family = $familyUser->familyOrCreate();

        if ($patient !== null) {
            $patient->families()->syncWithoutDetaching([$family->id]);
        }

        $this->call(AppointmentSeeder::class);

        if ($this->command !== null) {
            $this->command->info('Demo data: patient test@example.com, family family@example.com (password: password).');
        }
    }
}
