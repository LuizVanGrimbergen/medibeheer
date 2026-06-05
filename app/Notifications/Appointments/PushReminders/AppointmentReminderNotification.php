<?php

declare(strict_types=1);

namespace App\Notifications\Appointments\PushReminders;

use App\Support\Appointments\PushReminders\AppointmentReminderKind;
use App\Support\PushReminders\PushReminderRecipient;
use App\Support\PushReminders\ReminderTranslations;
use App\Support\PushReminders\WebPushReminderMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

final class AppointmentReminderNotification extends Notification
{
    public function __construct(
        public readonly AppointmentReminderKind $kind,
        public readonly array $appointment,
        public readonly PushReminderRecipient $recipient,
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $providerName = (string) ($this->appointment['provider_name'] ?? '');
        $doctorTypeLabel = (string) ($this->appointment['doctor_type_label'] ?? '');
        $startsAtDate = (string) ($this->appointment['starts_at_date'] ?? '');
        $startsAtTime = (string) ($this->appointment['starts_at_time'] ?? '');

        $titleKey = match ($this->kind) {
            AppointmentReminderKind::TwoDaysBefore => 'appointment_reminders.notification.two_days_before.title',
            AppointmentReminderKind::TwoHoursBefore => 'appointment_reminders.notification.two_hours_before.title',
        };

        $bodyKey = match ($this->kind) {
            AppointmentReminderKind::TwoDaysBefore => WebPushReminderMessage::bodyKeyForAudience(
                $this->recipient,
                'appointment_reminders.notification.two_days_before.body_patient',
                'appointment_reminders.notification.two_days_before.body_family',
            ),
            AppointmentReminderKind::TwoHoursBefore => WebPushReminderMessage::bodyKeyForAudience(
                $this->recipient,
                'appointment_reminders.notification.two_hours_before.body_patient',
                'appointment_reminders.notification.two_hours_before.body_family',
            ),
        };

        $bodyReplace = WebPushReminderMessage::withPatientName([
            'provider' => $providerName,
            'type' => $doctorTypeLabel,
            'date' => $startsAtDate,
            'time' => $startsAtTime,
        ], $this->recipient);

        return WebPushReminderMessage::forRecipient(
            $this->recipient,
            ReminderTranslations::trans($titleKey),
            ReminderTranslations::trans($bodyKey, $bodyReplace),
            sprintf(
                'appointment-%s-%d-%d',
                $this->kind->value,
                (int) ($this->appointment['appointment_id'] ?? 0),
                (int) $this->recipient->user->id,
            ),
            [
                'appointmentId' => (int) ($this->appointment['appointment_id'] ?? 0),
            ],
        );
    }
}
