<?php

declare(strict_types=1);

namespace App\Services\Privacy;

use App\Enums\UserConsentType;
use App\Models\User;
use App\Models\UserConsent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class UserConsentRecorder
{
    public function recordRegistrationConsents(User $user, Request $request): void
    {
        $policyVersion = (string) config('privacy.policy_version');
        $acceptedAt = Carbon::now();
        $ipAddress = $request->ip();

        foreach (UserConsentType::cases() as $type) {
            UserConsent::query()->create([
                'user_id' => $user->id,
                'type' => $type,
                'policy_version' => $policyVersion,
                'accepted_at' => $acceptedAt,
                'ip_address' => $ipAddress,
            ]);
        }
    }
}
