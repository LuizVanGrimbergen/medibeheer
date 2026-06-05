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

final class PreviewTwoHourAppointmentRemindersCommand extends Command
{
    use PreviewsAppointmentReminders;

    protected $signature = 'appointment:preview-two-hour-reminders';

    protected $description = 'List scheduled appointments starting in two hours and who would receive a push reminder.';

    public function handle(
        CandidatesQuery $candidates,
        RecipientsResolver $recipientsResolver,
        ReminderCache $reminderCache,
    ): int {
        $now = AppClock::now();
        $targetStartsAt = $now->addHours(2)->startOfMinute();

        $this->components->info(sprintf(
            'Server time: %s (%s)',
            $now->format('Y-m-d H:i:s'),
            AppClock::TIMEZONE,
        ));
        $this->components->info(sprintf(
            'Looking for scheduled appointments starting at %s.',
            $targetStartsAt->format('Y-m-d H:i'),
        ));

        return $this->previewAppointmentReminders(
            $candidates->eachTwoHoursBefore(...),
            $recipientsResolver,
            $reminderCache,
            AppointmentReminderKind::TwoHoursBefore,
            'No scheduled appointments starting in exactly two hours.',
        );
    }
}
