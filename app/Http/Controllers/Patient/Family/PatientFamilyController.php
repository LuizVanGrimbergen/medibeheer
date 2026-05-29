<?php

namespace App\Http\Controllers\Patient\Family;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Resources\Family\FamilyInvitationResource;
use App\Http\Resources\Patient\DoctorInvitationResource;
use App\Http\Resources\Patient\LinkedDoctorResource;
use App\Http\Resources\Patient\LinkedFamilyMemberResource;
use App\Http\Resources\Patient\PatientAcceptedMedicationPlanProposalResource;
use App\Http\Resources\Patient\PatientPendingMedicationPlanProposalResource;
use App\Services\Medications\MedicationPlanProposalService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientFamilyController extends Controller
{
    use AuthorizesPatientProfile;

    public function __construct(
        private readonly MedicationPlanProposalService $medicationPlanProposalService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $patient = $this->authorizePatientProfile($request);

        $pendingInvitations = $patient->familyInvitations()
            ->pending()
            ->orderByDesc('created_at')
            ->get();

        $pendingMedicationPlans = $this->medicationPlanProposalService
            ->pendingIncomingForPatient($request->user());

        $acceptedMedicationPlans = $this->medicationPlanProposalService
            ->acceptedForPatient($request->user());

        $pendingDoctorInvitations = $patient->doctorInvitations()
            ->pending()
            ->orderByDesc('created_at')
            ->get();

        $linkedDoctors = $patient->doctors()
            ->with('user')
            ->orderBy('doctors.id')
            ->get();

        $linkedFamilyMembers = $patient->families()
            ->with('user')
            ->orderBy('families.id')
            ->get();

        return Inertia::render('Patient/Family', [
            'family_invitations' => FamilyInvitationResource::collection($pendingInvitations)->resolve(),
            'pending_medication_plans' => PatientPendingMedicationPlanProposalResource::collection($pendingMedicationPlans)->resolve(),
            'accepted_medication_plans' => PatientAcceptedMedicationPlanProposalResource::collection($acceptedMedicationPlans)->resolve(),
            'family_invitation_store_url' => route('patient.family.invitations.store', absolute: false),
            'doctor_invitations' => DoctorInvitationResource::collection($pendingDoctorInvitations)->resolve(),
            'linked_doctors' => LinkedDoctorResource::collection($linkedDoctors)->resolve(),
            'linked_family_members' => LinkedFamilyMemberResource::collection($linkedFamilyMembers)->resolve(),
            'doctor_invitation_store_url' => route('patient.doctors.invitations.store', absolute: false),
        ]);
    }
}
