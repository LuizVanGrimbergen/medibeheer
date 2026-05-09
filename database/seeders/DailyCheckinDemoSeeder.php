<?php

namespace Database\Seeders;

use App\Enums\DailyMoodScore;
use App\Models\DailyCheckin;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class DailyCheckinDemoSeeder extends Seeder
{
    private const string DEMO_PATIENT_EMAIL = 'wellbeing-demo-patient@example.com';

    private const string DEMO_FAMILY_EMAIL = 'wellbeing-demo-family@example.com';

    public function run(): void
    {
        $patientUser = User::findByEmail(self::DEMO_PATIENT_EMAIL)
            ?? User::factory()->patient()->create([
                'name' => 'Demo Patient Welzijn',
                'email' => self::DEMO_PATIENT_EMAIL,
            ]);

        $patient = $patientUser->patient
            ?? Patient::query()->create([
                'user_id' => $patientUser->id,
                'streak_count' => 0,
            ]);

        $familyUser = User::findByEmail(self::DEMO_FAMILY_EMAIL)
            ?? User::factory()->familyMember()->create([
                'name' => 'Demo Familie Welzijn',
                'email' => self::DEMO_FAMILY_EMAIL,
            ]);

        $family = $familyUser->familyOrCreate();

        $patient->families()->syncWithoutDetaching([$family->id]);

        $start = Carbon::today()->subMonths(6)->startOfMonth();
        $end = Carbon::today();

        $created = 0;

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $crc = crc32($date->format('Y-m-d'));

            if ($crc % 13 === 0) {
                continue;
            }

            $weekday = $date->dayOfWeekIso;

            $mood = match (true) {
                $weekday <= 3 && ($crc % 7) <= 3 => DailyMoodScore::BAD,
                $weekday >= 5 && ($crc % 4) === 0 => DailyMoodScore::GOOD,
                ($crc % 6) <= 2 => DailyMoodScore::GOOD,
                ($crc % 6) <= 4 => DailyMoodScore::OK,
                default => DailyMoodScore::BAD,
            };

            DailyCheckin::query()->updateOrCreate(
                [
                    'patient_id' => $patient->id,
                    'checkin_date' => $date->format('Y-m-d'),
                ],
                [
                    'mood_score' => $mood,
                    'note' => ($crc % 9 === 1) ? 'Demo-notitie voor deze dag.' : null,
                ],
            );

            $created++;
        }

        if ($this->command !== null) {
            $this->command->info(sprintf(
                'Welzijn-demo: patient id %d, familie-user %s — %d check-ins tussen %s en %s (login wachtwoord: factory default, meestal `password`).',
                $patient->id,
                self::DEMO_FAMILY_EMAIL,
                $created,
                $start->toDateString(),
                $end->toDateString(),
            ));
        }
    }
}
