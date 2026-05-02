<?php

namespace App\Http\Controllers\Family\Concerns;

use App\Models\Family;
use Illuminate\Http\Request;

trait AuthorizesFamilyProfile
{
    protected function authorizeFamilyProfile(Request $request): Family
    {
        $user = $request->user();

        abort_unless($user !== null && $user->isFamilyMember(), 403);

        $family = $user->family;

        if ($family === null) {
            $family = Family::query()->firstOrCreate(
                ['user_id' => $user->id],
            );
        }

        $this->authorize('view', $family);

        return $family;
    }
}
