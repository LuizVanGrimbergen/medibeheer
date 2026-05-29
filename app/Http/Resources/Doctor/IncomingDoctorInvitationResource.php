<?php

namespace App\Http\Resources\Doctor;

use App\Models\DoctorInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DoctorInvitation */
class IncomingDoctorInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'patient_name' => (string) $this->patient->user->name,
            'expires_at' => $this->expires_at->toISOString(),
            'accept_url' => route('doctor.invitations.incoming.accept', ['incomingDoctorInvitation' => $this->public_id], absolute: false),
        ];
    }
}
