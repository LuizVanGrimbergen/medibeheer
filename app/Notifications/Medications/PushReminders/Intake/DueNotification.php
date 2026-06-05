<?php

declare(strict_types=1);

namespace App\Notifications\Medications\PushReminders\Intake;

use App\Support\Medications\MedicationIntakePushMarkUrl;
use App\Support\Medications\PatientMedicationReminderTranslations;
use App\Support\Medications\PatientMedicationReminderTypeLabel;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

final class DueNotification extends Notification
{
    public function __construct(
        public readonly array $slot,
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $name = (string) ($this->slot['name'] ?? '');
        $typeMedication = (string) ($this->slot['type_medication'] ?? '');

        $typeLabel = PatientMedicationReminderTypeLabel::forType($typeMedication);

        return (new WebPushMessage)
            ->title(PatientMedicationReminderTranslations::trans('patient_medication_reminders.notification.title'))
            ->body(PatientMedicationReminderTranslations::trans('patient_medication_reminders.notification.body', [
                'name' => $name,
                'type' => $typeLabel,
            ]))
            ->icon(url('/images/medibeheer-pwa.png'))
            ->tag(sprintf(
                'medication-intake-%d-%s',
                (int) ($this->slot['medication_schedule_id'] ?? 0),
                (string) ($this->slot['dose_time'] ?? ''),
            ))
            ->data([
                'markTakenUrl' => MedicationIntakePushMarkUrl::forSlot($this->slot),
                'medicationName' => $name,
            ]);
    }
}
