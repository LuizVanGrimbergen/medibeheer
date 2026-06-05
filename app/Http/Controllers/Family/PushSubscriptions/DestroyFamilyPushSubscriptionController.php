<?php

declare(strict_types=1);

namespace App\Http\Controllers\Family\PushSubscriptions;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DestroyFamilyPushSubscriptionController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorizeFamilyProfile($request);

        $user = $request->user();
        abort_if($user === null, 403);

        $user->pushSubscriptions()->delete();

        return response()->json(['deleted' => true]);
    }
}
