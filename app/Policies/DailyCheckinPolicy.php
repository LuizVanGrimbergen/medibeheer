<?php

namespace App\Policies;

use App\Models\DailyCheckin;
use App\Models\User;

class DailyCheckinPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPatient()
            || $user->isFamilyMember()
            || $user->isDoctor();
    }

    public function view(User $user, DailyCheckin $dailyCheckin): bool
    {
        return $user->can('view', $dailyCheckin->patient);
    }

    public function create(User $user): bool
    {
        return $user->isPatient();
    }

    public function update(User $user, DailyCheckin $dailyCheckin): bool
    {
        return $user->can('update', $dailyCheckin->patient);
    }
}
