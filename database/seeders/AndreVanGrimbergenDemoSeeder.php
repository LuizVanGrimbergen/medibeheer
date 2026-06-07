<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\DailyCheckinSymptom;
use App\Enums\DailyMoodScore;
use App\Enums\MedicationPrescriptionPickupStatus;
use App\Models\DailyCheckin;
use App\Models\Doctor;
use App\Models\Family;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationPrescription;
use App\Models\MedicationSchedule;
use App\Models\Patient;
use App\Models\User;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class AndreVanGrimbergenDemoSeeder extends Seeder
{
    public const string DEMO_PATIENT_NAME = 'Andre van Grimbergen';

    public const string DEMO_PATIENT_EMAIL = 'andre.vangrimbergen@voorbeeld.nl';

    private const int HISTORY_MONTHS = 6;

    private const int MAY_BAD_CHECKIN_DAY = 9;

    /** @var list<int> */
    private const array MAY_OK_CHECKIN_DAYS = [17, 24];

    private const int MAY_MISSED_INTAKE_DAY = 14;

    private const int EXPIRING_PRESCRIPTION_DAYS_FROM_TODAY = 5;

    public function run(): void
    {
        $familyUser = User::findByEmail(DatabaseSeeder::DEMO_FAMILY_EMAIL);

        if ($familyUser === null) {
            if ($this->command !== null) {
                $this->command->warn('AndreVanGrimbergenDemoSeeder skipped: familie-login marc.maas niet gevonden (run DatabaseSeeder eerst).');
            }

            return;
        }

        $family = $familyUser->familyOrCreate();

        $patientUser = User::findByEmail(self::DEMO_PATIENT_EMAIL)
            ?? User::factory()->patient()->create([
                'name' => self::DEMO_PATIENT_NAME,
                'email' => self::DEMO_PATIENT_EMAIL,
            ]);

        $patient = $patientUser->patient;

        if ($patient === null) {
            if ($this->command !== null) {
                $this->command->error(sprintf(
                    'AndreVanGrimbergenDemoSeeder: patiëntrecord ontbreekt voor %s.',
                    self::DEMO_PATIENT_EMAIL,
                ));
            }

            return;
        }

        $patient->families()->syncWithoutDetaching([$family->id]);

        if ($patient->medications()->doesntExist()) {
            $this->call(MedicationSeeder::class, false, [
                'patient' => $patient,
                'family' => $family,
            ]);
        }

        if ($patient->appointments()->doesntExist()) {
            $this->call(AppointmentSeeder::class, false, [
                'patient' => $patient,
            ]);
        }

        $mayStart = $this->demoMayStart();
        $mayEnd = $mayStart->copy()->endOfMonth();
        $juneStart = $this->demoJuneStart($mayStart);

        $this->seedMedicationIntakes($patient, $mayStart, $mayEnd);
        $this->seedDailyCheckins($patient, $mayStart, $mayEnd);
        $expiringPrescription = $this->seedExpiringPrescription($patient, $family);
        $this->linkToDemoDoctor($patient);

        if ($this->command !== null) {
            $expiringPrescriptionSummary = $expiringPrescription === null
                ? 'geen vervallend voorschrift'
                : sprintf(
                    'voorschrift %s verloopt op %s',
                    $expiringPrescription->medication?->name ?? 'medicatie',
                    $expiringPrescription->prescription_expiry_date?->toDateString() ?? '—',
                );

            $this->command->info(sprintf(
                'Andre demo: %s (%s) gekoppeld aan familie %s (%s) en dokter %s (%s). Mei %d: 1 gemiste medicatie, juni %d: alles op tijd ingenomen, check-ins 2× ok / 1× slecht / overige goed. %s. Afspraken: %d gepland, %d afgerond. Wachtwoord: password.',
                $patientUser->name,
                $patientUser->email,
                $familyUser->name,
                $familyUser->email,
                DoctorDemoSeeder::DEMO_DOCTOR_NAME,
                DoctorDemoSeeder::DEMO_DOCTOR_EMAIL,
                $mayStart->year,
                $juneStart->year,
                $expiringPrescriptionSummary,
                $patient->appointments()->where('status', AppointmentStatus::SCHEDULED)->count(),
                $patient->appointments()->where('status', AppointmentStatus::DONE)->count(),
            ));
        }
    }

    private function seedExpiringPrescription(Patient $patient, Family $family): ?MedicationPrescription
    {
        $medications = $patient->medications()->orderBy('id')->get();

        $medication = $medications->first(
            fn (Medication $candidate): bool => $candidate->name === 'Metformine',
        ) ?? $medications->first();

        if ($medication === null) {
            return null;
        }

        $prescription = MedicationPrescription::query()->updateOrCreate(
            [
                'patient_id' => $patient->id,
                'medication_id' => $medication->id,
            ],
            [
                'family_id' => $family->id,
                'prescription_expiry_date' => Carbon::today()
                    ->addDays(self::EXPIRING_PRESCRIPTION_DAYS_FROM_TODAY)
                    ->toDateString(),
                'is_last_in_batch' => true,
                'pickup_status' => MedicationPrescriptionPickupStatus::PENDING,
                'completed_at' => null,
            ],
        );

        $prescription->load('medication');

        return $prescription;
    }

    private function linkToDemoDoctor(Patient $patient): void
    {
        $doctorUser = User::findByEmail(DoctorDemoSeeder::DEMO_DOCTOR_EMAIL);

        if ($doctorUser === null) {
            return;
        }

        $doctor = Doctor::query()->firstOrCreate([
            'user_id' => $doctorUser->id,
        ]);

        $doctor->patients()->syncWithoutDetaching([$patient->id]);
    }

    private function demoMayStart(): Carbon
    {
        $today = Carbon::today();
        $year = $today->month >= 5 ? $today->year : $today->year - 1;

        return Carbon::create($year, 5, 1)->startOfDay();
    }

    private function demoJuneStart(Carbon $mayStart): Carbon
    {
        return $mayStart->copy()->addMonth()->startOfMonth();
    }

    private function seedMedicationIntakes(Patient $patient, Carbon $mayStart, Carbon $mayEnd): void
    {
        $occursOnDate = new MedicationScheduleOccursOnDate;
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        $historyStart = $today->copy()->subMonths(self::HISTORY_MONTHS)->startOfMonth();
        $juneStart = $this->demoJuneStart($mayStart);
        $juneEnd = $juneStart->copy()->endOfMonth();
        $seedEnd = $this->medicationIntakeSeedEnd($today, $juneStart, $juneEnd);
        $missedDate = $mayStart->copy()->day(self::MAY_MISSED_INTAKE_DAY)->toDateString();

        $schedules = MedicationSchedule::query()
            ->where('patient_id', $patient->id)
            ->with('medication')
            ->get();

        MedicationIntake::query()
            ->where('patient_id', $patient->id)
            ->delete();

        $firstSchedule = $schedules->first();
        $firstMissedDoseTime = $firstSchedule !== null
            ? ($occursOnDate->sortedDoseTimes($firstSchedule)[0] ?? null)
            : null;

        foreach ($schedules as $schedule) {
            $doseTimes = $occursOnDate->sortedDoseTimes($schedule);

            foreach (CarbonPeriod::create($historyStart, $seedEnd) as $date) {
                if (! $occursOnDate->isIntakeDueOn($schedule, $date)) {
                    continue;
                }

                foreach ($doseTimes as $doseTime) {
                    $trimmedDoseTime = trim($doseTime);

                    if ($trimmedDoseTime === '') {
                        continue;
                    }

                    $doseMoment = $this->doseMoment($date, $trimmedDoseTime);

                    if ($doseMoment === null) {
                        continue;
                    }

                    $isInDemoJune = $date->gte($juneStart) && $date->lte($juneEnd);

                    if ($doseMoment->gt($now) && ! $isInDemoJune) {
                        continue;
                    }

                    $isMissedDose = $date->gte($mayStart)
                        && $date->lte($mayEnd)
                        && $schedule->id === $firstSchedule?->id
                        && $date->toDateString() === $missedDate
                        && $trimmedDoseTime === $firstMissedDoseTime;

                    if ($isMissedDose) {
                        continue;
                    }

                    $intake = MedicationIntake::firstOrNewForScheduleDateAndDoseTime(
                        $schedule->id,
                        $date->toDateString(),
                        $trimmedDoseTime,
                    );

                    $intake->fill([
                        'patient_id' => $patient->id,
                        'medication_id' => $schedule->medication_id,
                        'taken_at' => $doseMoment->copy(),
                    ]);
                    $intake->save();
                }
            }
        }
    }

    private function seedDailyCheckins(Patient $patient, Carbon $mayStart, Carbon $mayEnd): void
    {
        $today = Carbon::today();
        $historyStart = $today->copy()->subMonths(self::HISTORY_MONTHS)->startOfMonth();

        foreach (CarbonPeriod::create($historyStart, $today) as $date) {
            $mood = $this->moodForDate($date, $mayStart, $mayEnd);

            $checkin = DailyCheckin::query()->updateOrCreate(
                [
                    'patient_id' => $patient->id,
                    'checkin_date' => $date->format('Y-m-d'),
                ],
                [
                    'mood_score' => $mood,
                    'note' => $this->noteForMood($mood),
                ],
            );

            $this->syncCheckinSymptoms($checkin, $this->symptomsForMood($mood));
        }
    }

    private function moodForDate(Carbon $date, Carbon $mayStart, Carbon $mayEnd): DailyMoodScore
    {
        if ($date->lt($mayStart) || $date->gt($mayEnd)) {
            return DailyMoodScore::GOOD;
        }

        if ($date->day === self::MAY_BAD_CHECKIN_DAY) {
            return DailyMoodScore::BAD;
        }

        if (in_array($date->day, self::MAY_OK_CHECKIN_DAYS, true)) {
            return DailyMoodScore::OK;
        }

        return DailyMoodScore::GOOD;
    }

    private function noteForMood(DailyMoodScore $mood): ?string
    {
        return match ($mood) {
            DailyMoodScore::GOOD => 'Rustige dag; medicatie goed ingenomen en energie voelt normaal.',
            DailyMoodScore::OK => 'Niet slecht, maar wel wat moe en stijf bij opstaan.',
            DailyMoodScore::BAD => 'Kortademig na een korte trap; extra rust genomen en Marc gebeld.',
        };
    }

    /**
     * @return list<DailyCheckinSymptom>
     */
    private function symptomsForMood(DailyMoodScore $mood): array
    {
        return match ($mood) {
            DailyMoodScore::GOOD => [],
            DailyMoodScore::OK => [
                DailyCheckinSymptom::FATIGUE,
                DailyCheckinSymptom::STIFF_OR_JOINT_PAIN,
            ],
            DailyMoodScore::BAD => [
                DailyCheckinSymptom::SHORTNESS_OF_BREATH,
                DailyCheckinSymptom::FATIGUE,
            ],
        };
    }

    /**
     * @param  list<DailyCheckinSymptom>  $symptoms
     */
    private function syncCheckinSymptoms(DailyCheckin $checkin, array $symptoms): void
    {
        $checkin->selectedSymptoms()->delete();

        foreach ($symptoms as $symptom) {
            $checkin->selectedSymptoms()->create([
                'symptom' => $symptom,
            ]);
        }
    }

    private function medicationIntakeSeedEnd(Carbon $today, Carbon $juneStart, Carbon $juneEnd): Carbon
    {
        if ($today->lt($juneStart)) {
            return $today->copy();
        }

        if ($today->lte($juneEnd)) {
            return $juneEnd->copy();
        }

        return $today->copy();
    }

    private function doseMoment(Carbon $date, string $doseTime): ?CarbonImmutable
    {
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $doseTime, $matches) !== 1) {
            return null;
        }

        $hours = (int) $matches[1];
        $minutes = (int) $matches[2];

        if ($hours > 23 || $minutes > 59) {
            return null;
        }

        return CarbonImmutable::parse(
            sprintf('%s %02d:%02d:00', $date->toDateString(), $hours, $minutes),
            MedicationIntakeClock::TIMEZONE,
        )->utc();
    }
}
