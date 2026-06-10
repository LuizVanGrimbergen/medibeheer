<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

final class PatientWebPushDemoSeeder extends Seeder
{
    public const string DEMO_PUSH_REMINDER_MEDICATION_NAME = 'Demo push herinnering';

    public function run(?User $patientUser = null): void
    {
        $patientUser ??= User::findByEmail(DatabaseSeeder::DEMO_PATIENT_EMAIL);

        if ($patientUser === null || ! $patientUser->isPatient()) {
            if ($this->command !== null) {
                $this->command->warn('PatientWebPushDemoSeeder skipped: demo patient user not found.');
            }

            return;
        }

        $patient = $patientUser->patient;

        if ($patient === null) {
            if ($this->command !== null) {
                $this->command->warn('PatientWebPushDemoSeeder skipped: patient record missing.');
            }

            return;
        }

        $this->removeLegacyDemoPushSubscriptions($patientUser);
        $this->removeLegacyDemoPushReminderMedications($patient);

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Demo push: %s (%s) — schakel meldingen in via het dashboard.',
                $patientUser->name,
                $patientUser->email,
            ));
        }
    }

    private function removeLegacyDemoPushSubscriptions(User $patientUser): void
    {
        $patientUser->pushSubscriptions()
            ->where('endpoint', 'like', '%push.example.test%')
            ->delete();
    }

    private function removeLegacyDemoPushReminderMedications(Patient $patient): void
    {
        $patient->medications()
            ->get()
            ->filter(fn (Medication $medication): bool => $medication->name === self::DEMO_PUSH_REMINDER_MEDICATION_NAME)
            ->each(fn (Medication $medication): bool => (bool) $medication->delete());
    }
}
