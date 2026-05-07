<?php

namespace App\Http\Controllers\Family\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Http\Resources\AppointmentTransportInvitationResource;
use App\Models\AppointmentTransportInvitation;
use App\Support\FamilyDashboardState;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyUpdatesController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(Request $request): Response
    {
        $family = $this->authorizeFamilyProfile($request);

        $activePatientId = FamilyDashboardState::activePatientId($request);

        $transportInvitations = AppointmentTransportInvitation::query()
            ->where('family_id', $family->id)
            ->pending()
            ->when(is_int($activePatientId), function ($query) use ($activePatientId) {
                $query->whereHas('appointment', fn ($q) => $q->where('patient_id', $activePatientId));
            })
            ->with(['appointment'])
            ->orderByDesc('invited_at')
            ->get();

        return Inertia::render('Family/Updates', [
            'transport_invitations' => AppointmentTransportInvitationResource::collection($transportInvitations)->resolve(),
        ]);
    }
}
