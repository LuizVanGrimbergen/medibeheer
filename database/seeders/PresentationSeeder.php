<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class PresentationSeeder extends Seeder
{
    public function run(): void
    {
        $familyUser = User::findByEmail(DatabaseSeeder::DEMO_FAMILY_EMAIL);

        if ($familyUser === null) {
            if ($this->command !== null) {
                $this->command->warn('PresentationSeeder skipped: run DatabaseSeeder first (familie-login ontbreekt).');
            }

            return;
        }

        $patientUser = User::findByEmail(DatabaseSeeder::DEMO_PATIENT_EMAIL);

        if ($patientUser === null) {
            if ($this->command !== null) {
                $this->command->warn('PresentationSeeder skipped: demo-patiënt Sophie Maas ontbreekt.');
            }

            return;
        }

        $patient = $patientUser->patient;

        if ($patient === null) {
            if ($this->command !== null) {
                $this->command->error(sprintf(
                    'PresentationSeeder: patiëntrecord ontbreekt voor %s.',
                    DatabaseSeeder::DEMO_PATIENT_EMAIL,
                ));
            }

            return;
        }

        $this->resetMedications($patient);

        $this->call(MedicationSeeder::class, false, [
            'patient' => $patient,
            'family' => $familyUser->familyOrCreate(),
            'presentationProfile' => true,
        ]);

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Presentation demo: %s heeft Metformine om 15:30 en Magnesiumcitraat om 21:00. Login: %s / password.',
                $patientUser->name,
                $patientUser->email,
            ));
        }
    }

    private function resetMedications(Patient $patient): void
    {
        $patient->medications()
            ->withTrashed()
            ->get()
            ->each(function (Medication $medication): void {
                $medication->forceDelete();
            });
    }
}
