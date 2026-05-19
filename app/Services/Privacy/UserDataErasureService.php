<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Models\User;
use App\Services\Audit\ActivityLogName;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

final class UserDataErasureService
{
    public function eraseUserRelatedRecords(User $user): void
    {
        $userId = $user->getKey();
        $userClass = $user::class;

        Activity::query()
            ->where(function ($query) use ($userClass, $userId): void {
                $query
                    ->where('causer_type', $userClass)
                    ->where('causer_id', $userId);
            })
            ->orWhere(function ($query) use ($userClass, $userId): void {
                $query
                    ->where('subject_type', $userClass)
                    ->where('subject_id', $userId);
            })
            ->delete();

        if ($user->patient !== null) {
            Activity::query()
                ->where('log_name', ActivityLogName::DATA)
                ->where('properties->patient_id', $user->patient->getKey())
                ->delete();
        }

        DB::table('sessions')
            ->where('user_id', $userId)
            ->delete();

        DB::table('password_reset_tokens')
            ->where('email', $user->email_hash)
            ->delete();
    }
}
