<?php

declare(strict_types=1);

namespace App\Console\Commands\Appointments\PushReminders;

use App\Console\Commands\Appointments\PushReminders\Concerns\PreviewsAppointmentReminders;
use App\Services\Appointments\PushReminders\CandidatesQuery;
use App\Services\PushReminders\RecipientsResolver;
use App\Support\AppClock;
use App\Support\Appointments\PushReminders\AppointmentReminderKind;
use App\Support\Appointments\PushReminders\ReminderCache;
use Illuminate\Console\Command;

final class PreviewTwoDayAppointmentRemindersCommand extends Command
{
    use PreviewsAppointmentReminders;

    protected $signature = 'appointment:preview-two-day-reminders';

    protected $description = 'List scheduled appointments two calendar days from today and who would receive a push reminder.';

    public function handle(
        CandidatesQuery $candidates,
        RecipientsResolver $recipientsResolver,
        ReminderCache $reminderCache,
    ): int {
        $now = AppClock::now();
        $targetDate = AppClock::today($now)->addDays(2);

        $this->components->info(sprintf(
            'Server time: %s (%s)',
            $now->format('Y-m-d H:i:s'),
            AppClock::TIMEZONE,
        ));
        $this->components->info(sprintf(
            'Looking for scheduled appointments on %s.',
            $targetDate->toDateString(),
        ));

        return $this->previewAppointmentReminders(
            $candidates->eachTwoDaysBefore(...),
            $recipientsResolver,
            $reminderCache,
            AppointmentReminderKind::TwoDaysBefore,
            'No scheduled appointments two calendar days from today.',
        );
    }
}
