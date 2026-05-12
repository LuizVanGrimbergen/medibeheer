<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public const string DEMO_PATIENT_EMAIL = 'sophie.maas@voorbeeld.nl';

    public const string DEMO_FAMILY_EMAIL = 'marc.maas@voorbeeld.nl';

    public function run(): void
    {
        $patientUser = User::findByEmail(self::DEMO_PATIENT_EMAIL)
            ?? User::factory()->patient()->create([
                'name' => 'Sophie Maas',
                'email' => self::DEMO_PATIENT_EMAIL,
            ]);

        $familyUser = User::findByEmail(self::DEMO_FAMILY_EMAIL)
            ?? User::factory()->familyMember()->create([
                'name' => 'Marc Maas',
                'email' => self::DEMO_FAMILY_EMAIL,
            ]);

        $patient = $patientUser->patient;
        $family = $familyUser->familyOrCreate();

        if ($patient === null) {
            if ($this->command !== null) {
                $this->command->error('DatabaseSeeder: patiëntrecord ontbreekt na aanmaken gebruiker.');
            }

            return;
        }

        $patient->families()->syncWithoutDetaching([$family->id]);

        $this->call(DailyCheckinDemoSeeder::class, false, ['patient' => $patient]);
        $this->call(AppointmentSeeder::class, false, ['patient' => $patient]);

        $patient->refresh();
        $checkinCount = $patient->dailyCheckins()->count();
        $patient->update([
            'streak_count' => min(max($checkinCount, 1), 120),
        ]);

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Demo: patiënt %s (%s), familie %s (%s). Wachtwoord: password.',
                $patientUser->name,
                $patientUser->email,
                $familyUser->name,
                $familyUser->email,
            ));
        }
    }
}
