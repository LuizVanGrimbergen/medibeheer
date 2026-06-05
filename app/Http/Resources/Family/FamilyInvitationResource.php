<?php

namespace App\Http\Resources\Family;

use App\Models\FamilyInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FamilyInvitation */
class FamilyInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'invited_email' => $this->invited_email,
            'expires_at' => $this->expires_at->toISOString(),
            'revoke_url' => route('patient.family.invitations.destroy', ['familyInvitation' => $this->public_id], absolute: false),
        ];
    }
}
