<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public const string DEMO_PATIENT_EMAIL = 'sophie.maas@voorbeeld.nl';

    public const string DEMO_FAMILY_EMAIL = 'marc.maas@voorbeeld.nl';

    /** @var list<array{name: string, email: string}> */
    public const array DEMO_LINKED_PATIENTS = [
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
    ];

    public function run(): void
    {
        $familyUser = User::findByEmail(self::DEMO_FAMILY_EMAIL)
            ?? User::factory()->familyMember()->create([
                'name' => 'Marc Maas',
                'email' => self::DEMO_FAMILY_EMAIL,
            ]);

        $family = $familyUser->familyOrCreate();
        $linkedPatients = [];

        foreach (self::DEMO_LINKED_PATIENTS as $demoPatient) {
            $patientUser = User::findByEmail($demoPatient['email'])
                ?? User::factory()->patient()->create([
                    'name' => $demoPatient['name'],
                    'email' => $demoPatient['email'],
                ]);

            $patient = $patientUser->patient;

            if ($patient === null) {
                if ($this->command !== null) {
                    $this->command->error(sprintf(
                        'DatabaseSeeder: patiëntrecord ontbreekt voor %s.',
                        $demoPatient['email'],
                    ));
                }

                return;
            }

            $patient->families()->syncWithoutDetaching([$family->id]);
            $linkedPatients[] = $patient;
        }

        $primaryPatient = $this->resolvePrimaryPatient($linkedPatients);

        if ($primaryPatient === null) {
            if ($this->command !== null) {
                $this->command->error(sprintf(
                    'DatabaseSeeder: primaire patiënt %s niet gevonden.',
                    self::DEMO_PATIENT_EMAIL,
                ));
            }

            return;
        }

        $primaryPatientUser = $primaryPatient->user;
        $primaryPatientUser?->refresh();

        $this->call(DailyCheckinDemoSeeder::class, false, ['patient' => $primaryPatient]);
        $this->call(AppointmentSeeder::class, false, ['patient' => $primaryPatient]);
        $this->call(MedicationSeeder::class, false, [
            'patient' => $primaryPatient,
            'family' => $family,
        ]);
        $this->call(PatientWebPushDemoSeeder::class, false, [
            'patientUser' => $primaryPatientUser,
        ]);
        $this->call(DoctorDemoSeeder::class);

        $primaryPatient->refresh();

        if ($this->command !== null) {
            $linkedPatientNames = collect($linkedPatients)
                ->map(fn (Patient $patient): string => (string) ($patient->user?->name ?? 'Patient'))
                ->join(', ');

            $this->command->info(sprintf(
                'Demo: familie %s (%s) met %d gekoppelde patiënten (%s). Primair: %s (%s). Dokter: %s (%s). Wachtwoord: password. Push-test: %s.',
                $familyUser->name,
                $familyUser->email,
                count($linkedPatients),
                $linkedPatientNames,
                $primaryPatientUser?->name ?? 'Patient',
                $primaryPatientUser?->email ?? self::DEMO_PATIENT_EMAIL,
                DoctorDemoSeeder::DEMO_DOCTOR_NAME,
                DoctorDemoSeeder::DEMO_DOCTOR_EMAIL,
                self::DEMO_PATIENT_EMAIL,
            ));
        }
    }

    /**
     * @param  list<Patient>  $linkedPatients
     */
    private function resolvePrimaryPatient(array $linkedPatients): ?Patient
    {
        foreach ($linkedPatients as $patient) {
            if ($patient->user?->email === self::DEMO_PATIENT_EMAIL) {
                return $patient;
            }
        }

        return $linkedPatients[0] ?? null;
    }
}
