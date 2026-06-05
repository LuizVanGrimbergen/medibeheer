<?php

declare(strict_types=1);

namespace App\Http\Controllers\Family\PushSubscriptions;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Http\Requests\Family\PushSubscriptions\StoreFamilyPushSubscriptionRequest;
use Illuminate\Http\JsonResponse;

final class StoreFamilyPushSubscriptionController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(StoreFamilyPushSubscriptionRequest $request): JsonResponse
    {
        $this->authorizeFamilyProfile($request);

        $user = $request->user();
        abort_if($user === null, 403);

        $validated = $request->validated();

        $user->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth'],
            $validated['contentEncoding'] ?? null,
        );

        return response()->json(['stored' => true]);
    }
}
