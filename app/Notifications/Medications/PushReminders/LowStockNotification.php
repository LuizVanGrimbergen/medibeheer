<?php

declare(strict_types=1);

namespace App\Notifications\Medications\PushReminders;

use App\Support\Medications\PatientMedicationReminderTypeLabel;
use App\Support\Medications\PushReminders\LowStock\ReminderTranslations;
use App\Support\Medications\PushReminders\PushReminderAudience;
use App\Support\Medications\PushReminders\PushReminderRecipient;
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

        $bodyKey = $this->recipient->audience === PushReminderAudience::Family
            ? 'medication_low_stock_reminders.notification.body_family'
            : 'medication_low_stock_reminders.notification.body_patient';

        $bodyReplace = [
            'name' => $name,
            'type' => $typeLabel,
            'days' => (string) $days,
        ];

        if ($this->recipient->audience === PushReminderAudience::Family) {
            $bodyReplace['patient'] = (string) ($this->recipient->patientName ?? '');
        }

        return (new WebPushMessage)
            ->title(ReminderTranslations::trans('medication_low_stock_reminders.notification.title'))
            ->body(ReminderTranslations::trans($bodyKey, $bodyReplace))
            ->icon(url('/images/medibeheer-pwa.png'))
            ->tag(sprintf(
                'medication-low-stock-%s-%d-%d',
                (string) ($this->medication['tier'] ?? 'critical'),
                (int) ($this->medication['medication_id'] ?? 0),
                (int) $this->recipient->user->id,
            ))
            ->data([
                'openUrl' => $this->recipient->openUrl,
                'medicationName' => $name,
            ]);
    }
}
