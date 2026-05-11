<?php

namespace App\Http\Resources;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appointment */
class PatientAppointmentResource extends JsonResource
{
    public static function collectForInertia(iterable $appointments): array
    {
        return static::collection($appointments)->resolve();
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'doctor_type' => $this->doctor_type->value,
            'provider_name' => (string) $this->provider_name,
            'street' => (string) $this->street,
            'house_number' => (string) $this->house_number,
            'postal_code' => (string) $this->postal_code,
            'city' => (string) $this->city,
            'starts_at' => $this->starts_at->toIso8601String(),
            'needs_transport' => (bool) $this->needs_transport,
            'transport_status' => $this->resource->transportStatus(
                (bool) ($this->has_pending_transport_invitation ?? false),
            )?->value,
            'transport_invited_family_ids' => $this->transportInvitations
                ->pluck('family_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
            'transport_family' => $this->resource->transportFamilyPayload(),
            'notes' => $this->notes,
            'doctor_visit_summary' => $this->doctor_visit_summary,
            'cancellation_reason' => $this->cancellation_reason,
            'status' => $this->status->value,
        ];
    }
}
