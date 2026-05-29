<?php

namespace App\Http\Controllers\Doctor\Patients;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctor\Concerns\AuthorizesDoctorProfile;
use App\Models\Patient;
use App\Services\Patient\PatientCareTeamLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyDoctorLinkedPatientController extends Controller
{
    use AuthorizesDoctorProfile;

    public function __invoke(
        Request $request,
        Patient $linkedPatient,
        PatientCareTeamLinkService $service,
    ): RedirectResponse {
        $doctor = $this->authorizeDoctorProfile($request);

        $service->unlinkPatient($doctor, $linkedPatient, $request->user());

        return redirect()
            ->route('doctor.patients')
            ->with('success', trans('doctor_invitation.flash.patient_unlinked'));
    }
}
