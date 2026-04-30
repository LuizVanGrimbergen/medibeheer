<?php

namespace App\Observers;

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
}
