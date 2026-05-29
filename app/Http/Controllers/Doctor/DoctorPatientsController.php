<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctor\Concerns\AuthorizesDoctorProfile;
use App\Http\Resources\Doctor\IncomingDoctorInvitationResource;
use App\Services\Doctor\DoctorInvitationService;
use App\Services\Doctor\DoctorLinkedPatientsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorPatientsController extends Controller
{
    use AuthorizesDoctorProfile;

    public function __construct(
        private readonly DoctorInvitationService $doctorInvitationService,
        private readonly DoctorLinkedPatientsService $linkedPatientsService,
    ) {}

    public function __invoke(Request $request): Response|RedirectResponse
    {
        $doctor = $this->authorizeDoctorProfile($request);

        if ($request->query('patient') !== null) {
            return redirect()->route('doctor.dashboard', $request->query());
        }

        return Inertia::render('Doctor/Patients/Index', [
            'patients' => $this->linkedPatientsService->listForDoctor($doctor),
            'incoming_invitations' => IncomingDoctorInvitationResource::collection(
                $this->doctorInvitationService->pendingIncomingForDoctor($request->user()),
            )->resolve(),
        ]);
    }
}
