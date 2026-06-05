<?php

declare(strict_types=1);

namespace App\Support\PushReminders;

use App\Models\User;

final readonly class PushReminderRecipient
{
    public function __construct(
        public User $user,
        public PushReminderAudience $audience,
        public string $openUrl,
        public ?string $patientName = null,
    ) {}
}
