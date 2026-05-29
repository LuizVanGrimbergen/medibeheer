<?php

namespace App\Http\Resources\Patient;

use App\Models\DoctorInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DoctorInvitation */
class DoctorInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'expires_at' => $this->expires_at->toISOString(),
            'revoke_url' => route('patient.doctors.invitations.destroy', ['doctorInvitation' => $this->public_id], absolute: false),
        ];
    }
}
