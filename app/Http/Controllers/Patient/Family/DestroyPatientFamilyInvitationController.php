<?php

namespace App\Http\Controllers\Patient\Family;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Models\FamilyInvitation;
use App\Services\FamilyInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyPatientFamilyInvitationController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        Request $request,
        FamilyInvitation $familyInvitation,
        FamilyInvitationService $service,
    ): RedirectResponse {
        $patient = $this->authorizePatientProfile($request);

        abort_unless((int) $familyInvitation->patient_id === (int) $patient->id, 404);

        $service->revoke($familyInvitation);

        return redirect()
            ->route('patient.family')
            ->with('success', trans('family_invitation.flash.revoked'));
    }
}
