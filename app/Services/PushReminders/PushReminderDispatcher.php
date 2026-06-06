<?php

declare(strict_types=1);

namespace App\Services\PushReminders;

use App\Support\PushReminders\PushReminderRecipient;
use DateInterval;
use DateTimeInterface;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification as NotificationFacade;

final class PushReminderDispatcher
{
    public const int DEFAULT_CACHE_TTL_DAYS = 90;

    public function trySend(
        PushReminderRecipient $recipient,
        string $cacheKey,
        DateInterval|DateTimeInterface|int $ttl,
        Notification $notification,
    ): bool {
        if (! Cache::add($cacheKey, true, $ttl)) {
            return false;
        }

        NotificationFacade::send($recipient->user, $notification);

        return true;
    }

    public function defaultTtl(): DateTimeInterface
    {
        return now()->addDays(self::DEFAULT_CACHE_TTL_DAYS);
    }

    public function ttlUntilEndOfDay(): DateInterval
    {
        return now()->diffAsCarbonInterval(now()->endOfDay());
    }
}
