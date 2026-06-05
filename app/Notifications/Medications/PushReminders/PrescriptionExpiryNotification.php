<?php

declare(strict_types=1);

namespace App\Notifications\Medications\PushReminders;

use App\Support\Medications\PatientMedicationReminderTypeLabel;
use App\Support\Medications\PushReminders\PrescriptionExpiry\ReminderTranslations;
use App\Support\Medications\PushReminders\PushReminderAudience;
use App\Support\Medications\PushReminders\PushReminderRecipient;
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

        $bodyKey = $this->recipient->audience === PushReminderAudience::Family
            ? 'prescription_expiry_reminders.notification.body_family'
            : 'prescription_expiry_reminders.notification.body_patient';

        $bodyReplace = [
            'name' => $name,
            'type' => $typeLabel,
            'days' => (string) $days,
        ];

        if ($this->recipient->audience === PushReminderAudience::Family) {
            $bodyReplace['patient'] = (string) ($this->recipient->patientName ?? '');
        }

        return (new WebPushMessage)
            ->title(ReminderTranslations::trans('prescription_expiry_reminders.notification.title'))
            ->body(ReminderTranslations::trans($bodyKey, $bodyReplace))
            ->icon(url('/images/medibeheer-pwa.png'))
            ->tag(sprintf(
                'prescription-expiry-%s-%d-%d',
                (string) ($this->prescription['tier'] ?? 'critical'),
                (int) ($this->prescription['prescription_id'] ?? 0),
                (int) $this->recipient->user->id,
            ))
            ->data([
                'openUrl' => $this->recipient->openUrl,
                'medicationName' => $name,
            ]);
    }
}
