<?php

namespace App\Http\Controllers\Family\Invitations;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Models\AppointmentTransportInvitation;
use App\Services\AppointmentTransportInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeclineAppointmentTransportInvitationController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __construct(
        private readonly AppointmentTransportInvitationService $appointmentTransportInvitationService,
    ) {}

    public function __invoke(Request $request, AppointmentTransportInvitation $transportInvitation): RedirectResponse
    {
        $family = $this->authorizeFamilyProfile($request);

        $this->authorize('decline', $transportInvitation);

        $this->appointmentTransportInvitationService->decline($transportInvitation, $family);

        return redirect()->route('family.appointments');
    }
}
