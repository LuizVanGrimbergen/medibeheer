<?php

namespace App\Policies;

use App\Models\AppointmentTransportInvitation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AppointmentTransportInvitationPolicy
{
    public function accept(User $user, AppointmentTransportInvitation $invitation): Response
    {
        return $this->authorizeInvitation($user, $invitation);
    }

    public function decline(User $user, AppointmentTransportInvitation $invitation): Response
    {
        return $this->authorizeInvitation($user, $invitation);
    }

    private function authorizeInvitation(User $user, AppointmentTransportInvitation $invitation): Response
    {
        $family = $user->family;
        $patientId = $invitation->appointment()->value('patient_id');

        if (! $user->isFamilyMember() || $family === null || $patientId === null) {
            return Response::deny();
        }

        $familyId = (int) $family->id;

        $notFound = (int) $invitation->family_id !== $familyId
            || ! $family->patients()->whereKey((int) $patientId)->exists();

        if ($notFound) {
            return Response::denyWithStatus(404);
        }

        return Response::allow();
    }
}
