<?php

namespace App\Observers;

use App\Models\Patient;
use App\Models\User;

class UserObserver
{
    public function creating(User $user): void
    {
        if ($user->public_id !== null) {
            return;
        }

        $user->public_id = (string) str()->uuid();
    }

    public function created(User $user): void
    {
        if (! $user->isPatient()) {
            return;
        }

        Patient::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['streak_count' => 0],
        );
    }
}
