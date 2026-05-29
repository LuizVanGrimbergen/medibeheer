<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorDemoSeeder extends Seeder
{
    public const string DEMO_DOCTOR_EMAIL = 'eva.vandijk@voorbeeld.nl';

    public const string DEMO_DOCTOR_NAME = 'Dr. Eva van Dijk';

    /** @var list<array{name: string, email: string}> */
    public const array DEMO_PATIENTS = [
        [
            'name' => 'Sophie Maas',
            'email' => 'sophie.maas@voorbeeld.nl',
        ],
        [
            'name' => 'Jan Maas',
            'email' => 'jan.maas@voorbeeld.nl',
        ],
        [
            'name' => 'Anna Maas',
            'email' => 'anna.maas@voorbeeld.nl',
        ],
        [
            'name' => 'Lucas Bakker',
            'email' => 'lucas.bakker@voorbeeld.nl',
        ],
        [
            'name' => 'Emma de Boer',
            'email' => 'emma.deboer@voorbeeld.nl',
        ],
        [
            'name' => 'Noah Jansen',
            'email' => 'noah.jansen@voorbeeld.nl',
        ],
        [
            'name' => 'Julia Visser',
            'email' => 'julia.visser@voorbeeld.nl',
        ],
        [
            'name' => 'Finn Smit',
            'email' => 'finn.smit@voorbeeld.nl',
        ],
        [
            'name' => 'Mila de Vries',
            'email' => 'mila.devries@voorbeeld.nl',
        ],
        [
            'name' => 'Daan Mulder',
            'email' => 'daan.mulder@voorbeeld.nl',
        ],
        [
            'name' => 'Lotte Meijer',
            'email' => 'lotte.meijer@voorbeeld.nl',
        ],
        [
            'name' => 'Bram de Graaf',
            'email' => 'bram.degraaf@voorbeeld.nl',
        ],
        [
            'name' => 'Isa Bos',
            'email' => 'isa.bos@voorbeeld.nl',
        ],
        [
            'name' => 'Sem Vos',
            'email' => 'sem.vos@voorbeeld.nl',
        ],
        [
            'name' => 'Eva Hendriks',
            'email' => 'eva.hendriks@voorbeeld.nl',
        ],
    ];

    public function run(): void
    {
        $doctorUser = User::findByEmail(self::DEMO_DOCTOR_EMAIL)
            ?? User::factory()->doctor()->create([
                'name' => self::DEMO_DOCTOR_NAME,
                'email' => self::DEMO_DOCTOR_EMAIL,
            ]);

        $doctor = Doctor::query()->firstOrCreate([
            'user_id' => $doctorUser->id,
        ]);

        $linkedPatients = [];

        foreach (self::DEMO_PATIENTS as $demoPatient) {
            $patientUser = User::findByEmail($demoPatient['email'])
                ?? User::factory()->patient()->create([
                    'name' => $demoPatient['name'],
                    'email' => $demoPatient['email'],
                ]);

            $patient = $patientUser->patient;

            if ($patient === null) {
                if ($this->command !== null) {
                    $this->command->warn(sprintf(
                        'DoctorDemoSeeder: patiëntrecord ontbreekt voor %s.',
                        $demoPatient['email'],
                    ));
                }

                continue;
            }

            $doctor->patients()->syncWithoutDetaching([$patient->id]);
            $linkedPatients[] = $patient;
        }

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Demo dokter: %s (%s) gekoppeld aan %d patiënt(en). Wachtwoord: password.',
                $doctorUser->name,
                $doctorUser->email,
                count($linkedPatients),
            ));
        }
    }
}
