<?php

namespace App\Http\Controllers\Patient\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Models\Doctor;
use App\Services\Patient\PatientCareTeamLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyPatientLinkedDoctorController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        Request $request,
        Doctor $linkedDoctor,
        PatientCareTeamLinkService $service,
    ): RedirectResponse {
        $patient = $this->authorizePatientProfile($request);

        $service->unlinkDoctor($patient, $linkedDoctor, $request->user());

        return redirect()
            ->route('patient.family')
            ->with('success', trans('doctor_invitation.flash.doctor_unlinked'));
    }
}
