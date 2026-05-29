<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patient\PushSubscriptions;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DestroyPatientPushSubscriptionController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(Request $request): JsonResponse
    {
        $this->authorizePatientProfile($request);

        $user = $request->user();
        abort_if($user === null, 403);

        $user->pushSubscriptions()->delete();

        return response()->json(['destroyed' => true]);
    }
}
