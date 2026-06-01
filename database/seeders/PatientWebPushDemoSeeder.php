<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationType;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\User;
use App\Support\Medications\MedicationIntakeClock;
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

        $this->seedDueNowReminderMedication($patient);

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Demo push: %s (%s) — medicatie "%s" dose-tijd %s. Schakel meldingen in via het dashboard.',
                $patientUser->name,
                $patientUser->email,
                self::DEMO_PUSH_REMINDER_MEDICATION_NAME,
                MedicationIntakeClock::now()->format('H:i'),
            ));
        }
    }

    private function removeLegacyDemoPushSubscriptions(User $patientUser): void
    {
        $patientUser->pushSubscriptions()
            ->where('endpoint', 'like', '%push.example.test%')
            ->delete();
    }

    private function seedDueNowReminderMedication(Patient $patient): void
    {
        $today = MedicationIntakeClock::today();
        $doseTime = MedicationIntakeClock::now()->format('H:i');

        $existing = $patient->medications()
            ->where('name', self::DEMO_PUSH_REMINDER_MEDICATION_NAME)
            ->first();

        if ($existing !== null) {
            $schedule = $existing->schedules()->first();

            if ($schedule !== null) {
                $schedule->update([
                    'dose_time' => $doseTime,
                    'snooze_time' => '30',
                ]);
            }

            $this->ensureDemoPushMedicationStock($existing);

            return;
        }

        $medication = $patient->medications()->create([
            'name' => self::DEMO_PUSH_REMINDER_MEDICATION_NAME,
            'dose' => '1',
            'dose_unit' => MedicationDoseUnit::PIECE,
            'type_medication' => MedicationType::PILL,
            'stock_pieces_per_package' => 30,
            'note' => 'Alleen voor lokaal testen van web push herinneringen.',
        ]);

        $schedule = $medication->schedules()->create([
            'patient_id' => $patient->id,
            'meal_timing' => MedicationMealTiming::UNRELATED,
            'intake_frequency' => MedicationIntakeFrequency::DAILY,
            'times_per_day' => '1',
            'dose_quantity' => '1',
            'dose_time' => $doseTime,
            'snooze_time' => '30',
            'start_date' => $today->toDateString(),
            'end_date' => $today->copy()->addYear()->toDateString(),
        ]);

        $schedule->syncIntakeWeekdays(null);

        $this->ensureDemoPushMedicationStock($medication);
    }

    private function ensureDemoPushMedicationStock(Medication $medication): void
    {
        if ($medication->stocks()->exists()) {
            return;
        }

        $medication->stocks()->create([
            'patient_id' => $medication->patient_id,
            'family_id' => $medication->family_id,
            'current_stock' => '30',
        ]);
    }
}
