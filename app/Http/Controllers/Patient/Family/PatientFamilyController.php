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
        $user = $request->user();

        return Inertia::render('Patient/Family/Index', [
            'family_invitation_store_url' => route('patient.family.invitations.store', absolute: false),
            'doctor_invitation_store_url' => route('patient.doctors.invitations.store', absolute: false),
            'family_invitations' => Inertia::defer(
                fn (): array => FamilyInvitationResource::collection(
                    $patient->familyInvitations()
                        ->pending()
                        ->orderByDesc('created_at')
                        ->get(),
                )->resolve(),
            ),
            'pending_medication_plans' => Inertia::defer(
                fn (): array => PatientPendingMedicationPlanProposalResource::collection(
                    $this->medicationPlanProposalService->pendingIncomingForPatient($user),
                )->resolve(),
            ),
            'accepted_medication_plans' => Inertia::defer(
                fn (): array => PatientAcceptedMedicationPlanProposalResource::collection(
                    $this->medicationPlanProposalService->acceptedForPatient($user),
                )->resolve(),
            ),
            'doctor_invitations' => Inertia::defer(
                fn (): array => DoctorInvitationResource::collection(
                    $patient->doctorInvitations()
                        ->pending()
                        ->orderByDesc('created_at')
                        ->get(),
                )->resolve(),
            ),
            'linked_doctors' => Inertia::defer(
                fn (): array => LinkedDoctorResource::collection(
                    $patient->doctors()
                        ->with('user')
                        ->orderBy('doctors.id')
                        ->get(),
                )->resolve(),
            ),
            'linked_family_members' => Inertia::defer(
                fn (): array => LinkedFamilyMemberResource::collection(
                    $patient->families()
                        ->with('user')
                        ->orderBy('families.id')
                        ->get(),
                )->resolve(),
            ),
        ]);
    }
}
