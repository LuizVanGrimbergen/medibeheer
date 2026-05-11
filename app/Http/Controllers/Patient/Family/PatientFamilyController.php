<?php

namespace App\Http\Controllers\Patient\Family;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Http\Resources\FamilyInvitationResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientFamilyController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(Request $request): Response
    {
        $patient = $this->authorizePatientProfile($request);

        $pendingInvitations = $patient->familyInvitations()
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Patient/Family', [
            'invitations' => FamilyInvitationResource::collection($pendingInvitations)->resolve(),
            'familyInvitationStoreUrl' => route('patient.family.invitations.store', absolute: false),
        ]);
    }
}
