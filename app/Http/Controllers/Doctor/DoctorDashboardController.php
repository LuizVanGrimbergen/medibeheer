<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctor\Concerns\AuthorizesDoctorProfile;
use App\Http\Controllers\Doctor\Concerns\ResolvesDoctorPatientOverview;
use App\Services\Doctor\DoctorLinkedPatientsService;
use App\Services\Doctor\DoctorPatientOverviewScreenService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorDashboardController extends Controller
{
    use AuthorizesDoctorProfile;
    use ResolvesDoctorPatientOverview;

    public function __construct(
        private readonly DoctorLinkedPatientsService $linkedPatientsService,
        private readonly DoctorPatientOverviewScreenService $patientOverviewScreenService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $doctor = $this->authorizeDoctorProfile($request);

        return Inertia::render('Doctor/Dashboard', [
            'patients' => $this->linkedPatientsService->listForDoctor($doctor),
            'patient_overview' => $this->resolveDoctorPatientOverview(
                $doctor,
                $request,
                $this->patientOverviewScreenService,
            ),
        ]);
    }
}
