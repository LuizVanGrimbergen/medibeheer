<?php

namespace App\Observers;

use App\Models\Doctor;
use App\Models\Family;
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
        if ($user->isPatient()) {
            Patient::query()->firstOrCreate(
                ['user_id' => $user->id],
            );

            return;
        }

        if ($user->isFamilyMember()) {
            Family::query()->firstOrCreate(
                ['user_id' => $user->id],
            );

            return;
        }

        if ($user->isDoctor()) {
            Doctor::query()->firstOrCreate(
                ['user_id' => $user->id],
            );
        }
    }
}
