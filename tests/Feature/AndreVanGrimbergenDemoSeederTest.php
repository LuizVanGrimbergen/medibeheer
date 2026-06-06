<?php

use App\Enums\DailyMoodScore;
use App\Models\Doctor;
use App\Models\MedicationIntake;
use App\Models\MedicationPrescription;
use App\Models\MedicationSchedule;
use App\Models\User;
use App\Services\Medications\PatientScheduledIntakesQuery;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use App\Support\Medications\MedicationUrgencyToneResolver;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Database\Seeders\AndreVanGrimbergenDemoSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DoctorDemoSeeder;

/**
 * @param  array{dose_time: string, snooze_minutes: int, taken_at: string|null}  $slot
 */
function medicationIntakeTakenWithinWindow(array $slot): bool
{
    if ($slot['taken_at'] === null) {
        return false;
    }

    if (preg_match('/^(\d{1,2}):(\d{2})$/', $slot['dose_time'], $matches) !== 1) {
        return true;
    }

    $doseStartMinutes = ((int) $matches[1] * 60) + (int) $matches[2];
    $takenAt = new DateTimeImmutable($slot['taken_at']);
    $formatter = new IntlDateFormatter(
        'en_GB',
        IntlDateFormatter::NONE,
        IntlDateFormatter::NONE,
        'Europe/Amsterdam',
        IntlDateFormatter::GREGORIAN,
        'HH:mm',
    );
    $formattedTakenAt = $formatter->format($takenAt);

    if ($formattedTakenAt === false || ! preg_match('/^(\d{1,2}):(\d{2})$/', $formattedTakenAt, $takenMatches)) {
        return true;
    }

    $takenMinutes = ((int) $takenMatches[1] * 60) + (int) $takenMatches[2];
    $windowEndMinutes = $doseStartMinutes + (int) $slot['snooze_minutes'];

    return $takenMinutes >= $doseStartMinutes && $takenMinutes <= $windowEndMinutes;
}

test('andre van grimbergen demo seeder links patient to marc maas with may demo data', function () {
    Carbon::setTestNow('2026-06-06 12:00:00');

    (new DatabaseSeeder)->run();

    $familyUser = User::findByEmail(DatabaseSeeder::DEMO_FAMILY_EMAIL);
    $patientUser = User::findByEmail(AndreVanGrimbergenDemoSeeder::DEMO_PATIENT_EMAIL);

    expect($familyUser)->not->toBeNull();
    expect($patientUser)->not->toBeNull();
    expect($patientUser?->name)->toBe(AndreVanGrimbergenDemoSeeder::DEMO_PATIENT_NAME);

    $patient = $patientUser?->patient;
    expect($patient)->not->toBeNull();

    $family = $familyUser?->familyOrCreate();
    expect($family->patients()->whereKey($patient?->id)->exists())->toBeTrue();

    $doctorUser = User::findByEmail(DoctorDemoSeeder::DEMO_DOCTOR_EMAIL);
    $doctor = Doctor::query()->where('user_id', $doctorUser?->id)->first();

    expect($doctor)->not->toBeNull();
    expect($doctor?->patients()->whereKey($patient?->id)->exists())->toBeTrue();

    $mayStart = Carbon::create(2026, 5, 1)->startOfDay();
    $mayEnd = $mayStart->copy()->endOfMonth();

    $mayCheckins = $patient
        ->dailyCheckins()
        ->whereDate('checkin_date', '>=', $mayStart->toDateString())
        ->whereDate('checkin_date', '<=', $mayEnd->toDateString())
        ->get();

    expect($mayCheckins)->toHaveCount(31);
    expect($mayCheckins->where('mood_score', DailyMoodScore::BAD))->toHaveCount(1);
    expect($mayCheckins->where('mood_score', DailyMoodScore::OK))->toHaveCount(2);
    expect($mayCheckins->where('mood_score', DailyMoodScore::GOOD))->toHaveCount(28);

    $outsideMayCheckins = $patient
        ->dailyCheckins()
        ->where(function ($query) use ($mayStart, $mayEnd): void {
            $query->whereDate('checkin_date', '<', $mayStart->toDateString())
                ->orWhereDate('checkin_date', '>', $mayEnd->toDateString());
        })
        ->get();

    expect($outsideMayCheckins->every(
        fn ($checkin): bool => $checkin->mood_score === DailyMoodScore::GOOD,
    ))->toBeTrue();

    $occursOnDate = new MedicationScheduleOccursOnDate;
    $schedules = MedicationSchedule::query()
        ->where('patient_id', $patient->id)
        ->get();

    $scheduledDosesInMay = 0;

    foreach ($schedules as $schedule) {
        foreach (CarbonPeriod::create($mayStart, $mayEnd) as $date) {
            if (! $occursOnDate->isIntakeDueOn($schedule, $date)) {
                continue;
            }

            $scheduledDosesInMay += count($occursOnDate->sortedDoseTimes($schedule));
        }
    }

    $takenDosesInMay = MedicationIntake::query()
        ->where('patient_id', $patient->id)
        ->whereDate('intake_date', '>=', $mayStart->toDateString())
        ->whereDate('intake_date', '<=', $mayEnd->toDateString())
        ->count();

    expect($scheduledDosesInMay - $takenDosesInMay)->toBe(1);

    $juneStart = Carbon::create(2026, 6, 1)->startOfDay();
    $juneEnd = $juneStart->copy()->endOfMonth();

    $scheduledDosesInJune = 0;

    foreach ($schedules as $schedule) {
        foreach (CarbonPeriod::create($juneStart, $juneEnd) as $date) {
            if (! $occursOnDate->isIntakeDueOn($schedule, $date)) {
                continue;
            }

            $scheduledDosesInJune += count($occursOnDate->sortedDoseTimes($schedule));
        }
    }

    $takenDosesInJune = MedicationIntake::query()
        ->where('patient_id', $patient->id)
        ->whereDate('intake_date', '>=', $juneStart->toDateString())
        ->whereDate('intake_date', '<=', $juneEnd->toDateString())
        ->count();

    expect($scheduledDosesInJune - $takenDosesInJune)->toBe(0);

    $juneSlots = app(PatientScheduledIntakesQuery::class)->slotsForPatientBetweenDates(
        $patient,
        CarbonImmutable::parse('2026-06-01', MedicationIntakeClock::TIMEZONE),
        CarbonImmutable::parse('2026-06-30', MedicationIntakeClock::TIMEZONE),
    );

    expect($juneSlots)->not->toBeEmpty();
    expect(collect($juneSlots)->every(
        fn (array $slot): bool => $slot['taken_at'] !== null && medicationIntakeTakenWithinWindow($slot),
    ))->toBeTrue();

    $urgencyToneResolver = app(MedicationUrgencyToneResolver::class);

    $expiringPrescriptions = MedicationPrescription::query()
        ->where('patient_id', $patient->id)
        ->whereNull('completed_at')
        ->get()
        ->filter(function (MedicationPrescription $prescription) use ($urgencyToneResolver): bool {
            $daysRemaining = $urgencyToneResolver->prescriptionExpiryDaysRemainingFor($prescription);

            return $daysRemaining !== null
                && $daysRemaining <= MedicationUrgencyToneResolver::WARNING_MAX_DAYS;
        });

    expect($expiringPrescriptions)->toHaveCount(1);
    expect($expiringPrescriptions->first()?->is_last_in_batch)->toBeTrue();
    expect($expiringPrescriptions->first()?->prescription_expiry_date?->toDateString())
        ->toBe('2026-06-11');
});
