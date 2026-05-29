<?php

namespace App\Http\Resources\Family;

use App\Models\FamilyInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FamilyInvitation */
class IncomingFamilyInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'patient_name' => (string) $this->patient->user->name,
            'expires_at' => $this->expires_at->toISOString(),
            'accept_url' => route('family.invitations.incoming.accept', ['incomingFamilyInvitation' => $this->public_id], absolute: false),
        ];
    }
}
