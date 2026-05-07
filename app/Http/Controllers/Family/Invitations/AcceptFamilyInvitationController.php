<?php

namespace App\Http\Controllers\Family\Invitations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Family\AcceptFamilyInvitationRequest;
use App\Services\FamilyInvitationService;
use Illuminate\Http\RedirectResponse;

class AcceptFamilyInvitationController extends Controller
{
    public function __invoke(
        AcceptFamilyInvitationRequest $request,
        FamilyInvitationService $service,
    ): RedirectResponse {
        $service->accept($request->user(), $request->normalizedCode());

        return redirect()
            ->route('family.overview')
            ->with('success', trans('family_invitation.flash.linked'));
    }
}
