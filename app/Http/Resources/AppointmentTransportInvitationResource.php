<?php

namespace App\Http\Resources;

use App\Models\AppointmentTransportInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AppointmentTransportInvitation */
class AppointmentTransportInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $appointment = $this->appointment;

        return [
            'id' => (int) $this->id,
            'invited_at' => $this->invited_at?->toIso8601String(),
            'accept_url' => route('family.transport-invitations.accept', ['transportInvitation' => $this->resource], absolute: false),
            'appointment' => $appointment === null
                ? null
                : [
                    'id' => (int) $appointment->id,
                    'provider_name' => (string) $appointment->provider_name,
                    'address' => (string) $appointment->address,
                    'starts_at' => $appointment->starts_at->toIso8601String(),
                    'needs_transport' => (bool) $appointment->needs_transport,
                ],
        ];
    }
}
