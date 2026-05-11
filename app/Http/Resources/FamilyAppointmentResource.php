<?php

namespace App\Http\Resources;

use App\Models\Appointment;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appointment */
class FamilyAppointmentResource extends JsonResource
{
    public function __construct(
        Appointment $resource,
        private readonly Family $family,
    ) {
        parent::__construct($resource);
    }

    public static function collectForInertia(iterable $appointments, Family $family): array
    {
        return collect($appointments)
            ->map(fn (Appointment $appointment): array => (new self($appointment, $family))->toArray(request()))
            ->values()
            ->all();
    }

    public function toArray(Request $request): array
    {
        $appointment = $this->resource;
        assert($appointment instanceof Appointment);

        $invitation = $appointment->transportInvitations
            ->firstWhere('family_id', $this->family->id);
        $pendingInvitation = $invitation !== null && $invitation->isPending() ? $invitation : null;
        $acceptUrl = null;
        $declineUrl = null;

        if ($pendingInvitation !== null && $appointment->family_id === null) {
            $acceptUrl = route('family.transport-invitations.accept', ['transportInvitation' => $pendingInvitation], absolute: false);
            $declineUrl = route('family.transport-invitations.decline', ['transportInvitation' => $pendingInvitation], absolute: false);
        }

        return [
            'id' => (int) $appointment->id,
            'doctor_type' => $appointment->doctor_type->value,
            'provider_name' => (string) $appointment->provider_name,
            'street' => (string) $appointment->street,
            'house_number' => (string) $appointment->house_number,
            'postal_code' => (string) $appointment->postal_code,
            'city' => (string) $appointment->city,
            'starts_at' => $appointment->starts_at->toIso8601String(),
            'needs_transport' => (bool) $appointment->needs_transport,
            'transport_status' => $appointment->transportStatus($pendingInvitation !== null)?->value,
            'doctor_visit_summary' => $appointment->doctor_visit_summary,
            'cancellation_reason' => $appointment->cancellation_reason,
            'status' => $appointment->status->value,
            'transport_family' => $appointment->transportFamilyPayload(),
            'transport_invitation' => $invitation === null
                ? null
                : [
                    'id' => (int) $invitation->id,
                    'invited_at' => $invitation->invited_at?->toIso8601String(),
                    'is_pending' => $invitation->isPending(),
                    'accept_url' => $acceptUrl,
                    'decline_url' => $declineUrl,
                ],
        ];
    }
}
