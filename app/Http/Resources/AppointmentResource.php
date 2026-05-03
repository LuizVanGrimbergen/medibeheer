<?php

namespace App\Http\Resources;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appointment */
class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'doctor_type' => $this->doctor_type->value,
            'provider_name' => $this->provider_name,
            'address' => $this->address,
            'starts_at' => $this->starts_at->toIso8601String(),
            'notes' => $this->notes,
            'doctor_visit_summary' => $this->doctor_visit_summary,
            'cancellation_reason' => $this->cancellation_reason,
            'status' => $this->status->value,
        ];
    }
}
