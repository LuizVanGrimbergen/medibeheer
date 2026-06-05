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

final class LowStockNotification extends Notification
{
    public function __construct(
        public readonly array $medication,
        public readonly PushReminderRecipient $recipient,
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $name = (string) ($this->medication['name'] ?? '');
        $typeMedication = (string) ($this->medication['type_medication'] ?? '');
        $days = (int) ($this->medication['supply_estimate_days'] ?? 0);
        $typeLabel = PatientMedicationReminderTypeLabel::forType($typeMedication);

        $bodyKey = WebPushReminderMessage::bodyKeyForAudience(
            $this->recipient,
            'medication_low_stock_reminders.notification.body_patient',
            'medication_low_stock_reminders.notification.body_family',
        );

        $bodyReplace = WebPushReminderMessage::withPatientName([
            'name' => $name,
            'type' => $typeLabel,
            'days' => (string) $days,
        ], $this->recipient);

        return WebPushReminderMessage::forRecipient(
            $this->recipient,
            ReminderTranslations::trans('medication_low_stock_reminders.notification.title'),
            ReminderTranslations::trans($bodyKey, $bodyReplace),
            sprintf(
                'medication-low-stock-%s-%d-%d',
                (string) ($this->medication['tier'] ?? 'critical'),
                (int) ($this->medication['medication_id'] ?? 0),
                (int) $this->recipient->user->id,
            ),
            [
                'medicationName' => $name,
            ],
        );
    }
}
