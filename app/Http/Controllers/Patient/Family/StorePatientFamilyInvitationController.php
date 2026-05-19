<?php

namespace App\Http\Controllers\Patient\Family;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Family\StoreFamilyInvitationRequest;
use App\Services\Family\FamilyInvitationService;
use Illuminate\Http\RedirectResponse;

class StorePatientFamilyInvitationController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        StoreFamilyInvitationRequest $request,
        FamilyInvitationService $service,
    ): RedirectResponse {
        $patient = $this->authorizePatientProfile($request);

        $service->create(
            $patient,
            $request->validated('email'),
            $request->user(),
        );

        return redirect()
            ->route('patient.family')
            ->with('success', trans('family_invitation.flash.sent'));
    }
}
