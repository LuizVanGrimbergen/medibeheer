<?php

namespace App\Http\Controllers\Patient\Family;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Models\User;
use App\Services\Patient\PatientCareTeamLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyPatientLinkedFamilyMemberController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        Request $request,
        User $linkedFamilyMember,
        PatientCareTeamLinkService $service,
    ): RedirectResponse {
        $patient = $this->authorizePatientProfile($request);

        $service->unlinkFamilyMember($patient, $linkedFamilyMember, $request->user());

        return redirect()
            ->route('patient.family')
            ->with('success', trans('family_invitation.flash.member_unlinked'));
    }
}
