<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\PushSubscriptions;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\PushSubscriptions\StorePatientPushSubscriptionRequest;
use Illuminate\Http\JsonResponse;

final class StorePatientPushSubscriptionController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(StorePatientPushSubscriptionRequest $request): JsonResponse
    {
        $this->authorizePatientProfile($request);

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
