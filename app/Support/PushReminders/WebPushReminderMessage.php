<?php

declare(strict_types=1);

namespace App\Support\PushReminders;

use NotificationChannels\WebPush\WebPushMessage;

final class WebPushReminderMessage
{
    public static function bodyKeyForAudience(
        PushReminderRecipient $recipient,
        string $patientKey,
        string $familyKey,
    ): string {
        return $recipient->audience === PushReminderAudience::Family
            ? $familyKey
            : $patientKey;
    }

    /**
     * @param  array<string, string>  $replace
     * @return array<string, string>
     */
    public static function withPatientName(array $replace, PushReminderRecipient $recipient): array
    {
        if ($recipient->audience === PushReminderAudience::Family) {
            $replace['patient'] = (string) ($recipient->patientName ?? '');
        }

        return $replace;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function forRecipient(
        PushReminderRecipient $recipient,
        string $title,
        string $body,
        string $tag,
        array $data = [],
    ): WebPushMessage {
        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->icon(url('/images/medibeheer-pwa.png'))
            ->tag($tag)
            ->data([
                'openUrl' => $recipient->openUrl,
                ...$data,
            ]);
    }
}
