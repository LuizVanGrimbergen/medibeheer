<?php

namespace App\Http\Controllers\Patient\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Requests\Patient\Doctors\StoreDoctorInvitationRequest;
use App\Services\Doctor\DoctorInvitationService;
use Illuminate\Http\RedirectResponse;

class StorePatientDoctorInvitationController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        StoreDoctorInvitationRequest $request,
        DoctorInvitationService $service,
    ): RedirectResponse {
        $patient = $this->authorizePatientProfile($request);

        $service->create(
            $patient,
            $request->validated('email'),
            $request->user(),
        );

        return redirect()
            ->route('patient.family')
            ->with('success', trans('doctor_invitation.flash.sent'));
    }
}
