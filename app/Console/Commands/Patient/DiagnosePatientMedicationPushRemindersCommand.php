<?php

declare(strict_types=1);

namespace App\Console\Commands\Patient;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Medications\PatientMedicationDueRemindersService;
use App\Services\Medications\PatientScheduledIntakesQuery;
use App\Support\Medications\DoseTime;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationIntakeReminderTiming;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

final class DiagnosePatientMedicationPushRemindersCommand extends Command
{
    protected $signature = 'patient:diagnose-medication-push-reminders {user? : Patient user id}';

    protected $description = 'Explain why medication push reminders may not arrive (schedules, exact time, queue, VAPID).';

    public function handle(
        PatientScheduledIntakesQuery $scheduledIntakes,
        PatientMedicationDueRemindersService $reminders,
    ): int {
        $now = MedicationIntakeClock::now();
        $todayKey = MedicationIntakeClock::today()->toDateString();

        $this->components->info('Server time: '.$now->format('Y-m-d H:i:s').' ('.MedicationIntakeClock::TIMEZONE.')');

        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');
        $this->line('VAPID public key: '.(is_string($publicKey) && $publicKey !== '' ? 'configured' : 'MISSING'));
        $this->line('VAPID private key: '.(is_string($privateKey) && $privateKey !== '' ? 'configured' : 'MISSING'));
        $this->line('APP_URL: '.config('app.url'));
        $this->line('Queue: '.config('queue.default'));
        $this->line('Pending queue jobs: '.Queue::size());
        $this->line('Failed jobs: '.DB::table('failed_jobs')->count());
        $this->line('Scheduled in routes/console.php: patient:send-medication-due-reminders (every minute).');
        $this->line('Local: `php artisan schedule:work` (or `composer run dev`). Production: cron `schedule:run` every minute.');
        $this->line('Queue worker: not required for medication push (sent directly from scheduler).');
        $this->line('Manual test: `patient:send-test-push-notification` bypasses scheduler and exact minute.');
        $this->newLine();

        $user = $this->resolveUser();

        if ($user === null) {
            $this->components->error('No patient with a push subscription found. Enable reminders on the patient dashboard first.');

            return self::FAILURE;
        }

        $patient = $user->patient;

        if ($patient === null) {
            $this->components->error('Patient record missing.');

            return self::FAILURE;
        }

        $subscriptionCount = $user->pushSubscriptions()
            ->where('endpoint', 'not like', '%push.example.test%')
            ->count();

        $this->components->info("{$user->name} (user #{$user->id})");
        $this->line("Push subscriptions: {$subscriptionCount}".($subscriptionCount === 0 ? ' — enable reminders in the PWA dashboard' : ''));

        $dueNow = $reminders->dueReminderSlotsForPatient($patient);

        $this->newLine();
        $this->line('Today\'s medication slots:');

        $slots = $scheduledIntakes->forPatientOnDate($patient);

        if ($slots === []) {
            $this->warn('  No schedules for today (check start_date, frequency, weekdays).');

            return self::SUCCESS;
        }

        foreach ($slots as $slot) {
            $scheduleId = (int) $slot['medication_schedule_id'];
            $doseTime = (string) $slot['dose_time'];
            $cacheKey = 'patient-medication-due-reminder:'
                .$patient->id.":{$scheduleId}:{$doseTime}:{$todayKey}";
            $alreadySent = Cache::has($cacheKey);
            $exact = MedicationIntakeReminderTiming::isExactDoseTimeMinute($doseTime);
            $taken = $slot['taken_at'] !== null;

            $status = match (true) {
                $taken => 'already taken',
                $alreadySent => 'push already sent today (cache — test does not set this)',
                $exact => 'DUE NOW — needs schedule:work this minute',
                default => 'waiting for exact dose_time minute (only that HH:MM)',
            };

            $this->line(sprintf(
                '  - %s at %s | %s',
                (string) ($slot['name'] ?? ''),
                $doseTime,
                $status,
            ));
        }

        $this->newLine();

        if ($dueNow !== []) {
            $this->components->info('Slots that would send right now: '.count($dueNow));
        } else {
            $this->components->warn('Nothing due right now. Set dose_time to the next minute and keep schedule:work running, or use patient:send-test-push-notification.');
        }

        if ($subscriptionCount > 0) {
            $this->warnIfTodaysDoseTimesAlreadyPassed($slots, $patient->id, $todayKey, $now);
        }

        return self::SUCCESS;
    }

    /** @param  list<array<string, mixed>>  $slots */
    private function warnIfTodaysDoseTimesAlreadyPassed(array $slots, int $patientId, string $todayKey, CarbonInterface $now): void
    {
        $passedWithoutPush = [];

        foreach ($slots as $slot) {
            if ($slot['taken_at'] !== null) {
                continue;
            }

            $doseTime = (string) ($slot['dose_time'] ?? '');
            $parsed = DoseTime::tryFrom($doseTime);

            if ($parsed === null) {
                continue;
            }

            $doseMinute = $now->copy()->startOfDay()->addMinutes($parsed->minutesSinceMidnight());

            if ($doseMinute->gte($now->copy()->startOfMinute())) {
                continue;
            }

            $scheduleId = (int) ($slot['medication_schedule_id'] ?? 0);
            $cacheKey = 'patient-medication-due-reminder:'
                .$patientId.":{$scheduleId}:{$doseTime}:{$todayKey}";

            if (! Cache::has($cacheKey)) {
                $passedWithoutPush[] = (string) ($slot['name'] ?? '').' at '.$doseTime;
            }
        }

        if ($passedWithoutPush === []) {
            return;
        }

        $this->newLine();
        $this->components->warn(
            'Innametijd(en) vandaag al gepasseerd vóór herinneringen aan stonden (geen push verstuurd):',
        );

        foreach ($passedWithoutPush as $line) {
            $this->line('  - '.$line);
        }

        $this->line('  Zet inneming op het volgende hele minuut (bijv. '.$now->copy()->addMinute()->format('H:i').') om de echte herinnering te testen.');
    }

    private function resolveUser(): ?User
    {
        $userId = $this->argument('user');

        if ($userId !== null) {
            $user = User::query()->find((int) $userId);

            if ($user === null || ! $user->isPatient()) {
                return null;
            }

            return $user;
        }

        return User::query()
            ->where('role', UserRole::PATIENT)
            ->whereHas('pushSubscriptions', function ($query): void {
                $query->where('endpoint', 'not like', '%push.example.test%');
            })
            ->orderBy('id')
            ->first();
    }
}
