<?php

namespace App\Http\Controllers\Patient\Family;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Resources\Family\FamilyInvitationResource;
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
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get();

        $pendingMedicationPlans = $this->medicationPlanProposalService
            ->pendingIncomingForPatient($request->user());

        $acceptedMedicationPlans = $this->medicationPlanProposalService
            ->acceptedForPatient($request->user());

        return Inertia::render('Patient/Family', [
            'invitations' => FamilyInvitationResource::collection($pendingInvitations)->resolve(),
            'pending_medication_plans' => PatientPendingMedicationPlanProposalResource::collection($pendingMedicationPlans)->resolve(),
            'accepted_medication_plans' => PatientAcceptedMedicationPlanProposalResource::collection($acceptedMedicationPlans)->resolve(),
            'familyInvitationStoreUrl' => route('patient.family.invitations.store', absolute: false),
        ]);
    }
}
