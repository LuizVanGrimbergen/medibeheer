<?php

declare(strict_types=1);

namespace App\Console\Commands\Patient;

use App\Enums\UserRole;
use App\Models\User;
use App\Notifications\Patient\MedicationIntakeDueNotification;
use App\Notifications\Patient\MedicationIntakeMissedNotification;
use App\Services\Medications\PatientScheduledIntakesQuery;
use App\Support\Medications\MedicationIntakeClock;
use Database\Seeders\PatientWebPushDemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

final class SendPatientTestPushNotificationCommand extends Command
{
    protected $signature = 'patient:send-test-push-notification
                            {user? : User id (default: first patient with push subscription)}
                            {--missed : Send the missed-window reminder (opens dashboard) instead of the due reminder}
                            {--with-demo : Seed demo medication at current time (local testing only)}';

    protected $description = 'Send one medication reminder push immediately (bypasses cache and exact-minute timing).';

    public function handle(PatientScheduledIntakesQuery $scheduledIntakes): int
    {
        if ($this->option('with-demo')) {
            $this->call('db:seed', [
                '--class' => PatientWebPushDemoSeeder::class,
                '--no-interaction' => true,
            ]);
        }

        $user = $this->resolveUser();

        if ($user === null) {
            $this->components->error('No patient with a push subscription found. Enable reminders on the patient dashboard first.');

            return self::FAILURE;
        }

        $patient = $user->patient;

        if ($patient === null) {
            $this->components->error('Patient record missing for user '.$user->id.'.');

            return self::FAILURE;
        }

        $slots = collect($scheduledIntakes->forPatientOnDate($patient));

        $slot = $this->option('with-demo')
            ? $slots->first(fn (array $row): bool => $row['taken_at'] === null
                && str_contains((string) ($row['name'] ?? ''), PatientWebPushDemoSeeder::DEMO_PUSH_REMINDER_MEDICATION_NAME))
            : null;

        if ($slot === null) {
            $slot = $slots->first(fn (array $row): bool => $row['taken_at'] === null);
        }

        if ($slot === null) {
            $this->components->error('No open medication intake slot found for today.');
            $this->components->warn('Tip: use patient:preview-medication-due-reminders to see schedule-based due slots, or --with-demo for local demo data.');

            return self::FAILURE;
        }

        $todayKey = MedicationIntakeClock::today()->toDateString();
        $scheduleId = (int) $slot['medication_schedule_id'];
        $doseTime = (string) $slot['dose_time'];
        $isMissed = (bool) $this->option('missed');
        $cachePrefix = $isMissed
            ? 'patient-medication-missed-reminder'
            : 'patient-medication-due-reminder';

        Cache::forget("{$cachePrefix}:{$patient->id}:{$scheduleId}:{$doseTime}:{$todayKey}");

        if ($isMissed) {
            Notification::sendNow($user, new MedicationIntakeMissedNotification($slot));
        }

        if (! $isMissed) {
            Notification::sendNow($user, new MedicationIntakeDueNotification($slot));
        }

        $kind = $isMissed ? 'missed (dashboard)' : 'due (mark intake)';

        $this->components->info(sprintf(
            'Test %s push sent to %s (%s) for "%s" at %s.',
            $kind,
            $user->name,
            $user->id,
            (string) ($slot['name'] ?? ''),
            $doseTime,
        ));

        if ($isMissed) {
            $this->components->warn('Tap the notification to open the patient dashboard. On iPhone, use the Home Screen PWA icon.');
        }

        if (! $isMissed) {
            $this->components->warn('Tap the notification itself to register intake. On iPhone, use the Home Screen PWA icon.');
        }

        return self::SUCCESS;
    }

    private function resolveUser(): ?User
    {
        $userId = $this->argument('user');

        if ($userId !== null) {
            $user = User::query()->find((int) $userId);

            if ($user !== null && $user->isPatient()) {
                return $user;
            }

            return null;
        }

        return User::query()
            ->where('role', UserRole::PATIENT)
            ->whereHas('pushSubscriptions', function ($query): void {
                $query->where('endpoint', 'not like', '%push.example.test%');
            })
            ->first();
    }
}
