<?php

declare(strict_types=1);

namespace App\Notifications\Medications\PushReminders;

use App\Support\Medications\PatientMedicationReminderTypeLabel;
use App\Support\PushReminders\PushReminderRecipient;
use App\Support\PushReminders\ReminderTranslations;
use App\Support\PushReminders\WebPushReminderMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

final class PrescriptionExpiryNotification extends Notification
{
    public function __construct(
        public readonly array $prescription,
        public readonly PushReminderRecipient $recipient,
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $name = (string) ($this->prescription['name'] ?? '');
        $typeMedication = (string) ($this->prescription['type_medication'] ?? '');
        $days = (int) ($this->prescription['days_remaining'] ?? 0);
        $typeLabel = PatientMedicationReminderTypeLabel::forType($typeMedication);

        $bodyKey = WebPushReminderMessage::bodyKeyForAudience(
            $this->recipient,
            'prescription_expiry_reminders.notification.body_patient',
            'prescription_expiry_reminders.notification.body_family',
        );

        $bodyReplace = WebPushReminderMessage::withPatientName([
            'name' => $name,
            'type' => $typeLabel,
            'days' => (string) $days,
        ], $this->recipient);

        return WebPushReminderMessage::forRecipient(
            $this->recipient,
            ReminderTranslations::trans('prescription_expiry_reminders.notification.title'),
            ReminderTranslations::trans($bodyKey, $bodyReplace),
            sprintf(
                'prescription-expiry-%s-%d-%d',
                (string) ($this->prescription['tier'] ?? 'critical'),
                (int) ($this->prescription['prescription_id'] ?? 0),
                (int) $this->recipient->user->id,
            ),
            [
                'medicationName' => $name,
            ],
        );
    }
}
