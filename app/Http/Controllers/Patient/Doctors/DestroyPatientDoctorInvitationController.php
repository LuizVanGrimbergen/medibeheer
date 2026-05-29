<?php

namespace App\Http\Controllers\Patient\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Models\DoctorInvitation;
use App\Services\Doctor\DoctorInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyPatientDoctorInvitationController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        Request $request,
        DoctorInvitation $doctorInvitation,
        DoctorInvitationService $service,
    ): RedirectResponse {
        $this->authorizePatientProfile($request);

        $service->revoke($doctorInvitation, $request->user());

        return redirect()
            ->route('patient.family')
            ->with('success', trans('doctor_invitation.flash.revoked'));
    }
}
