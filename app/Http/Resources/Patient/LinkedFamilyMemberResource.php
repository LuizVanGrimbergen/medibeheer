<?php

namespace App\Http\Resources\Patient;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Family */
class LinkedFamilyMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->user->public_id,
            'name' => (string) $this->user->name,
            'unlink_url' => route('patient.family.members.destroy', ['linkedFamilyMember' => $this->user->public_id], absolute: false),
        ];
    }
}
