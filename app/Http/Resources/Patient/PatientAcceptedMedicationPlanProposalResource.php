<?php

namespace App\Http\Resources\Patient;

use App\Models\MedicationPlanProposal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MedicationPlanProposal */
class PatientAcceptedMedicationPlanProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $firstItem = $this->items->first();

        return [
            'id' => $this->id,
            'medication_name' => $firstItem?->name,
            'family_member_name' => (string) ($this->family?->user?->name ?? ''),
            'accepted_at' => $this->accepted_at?->toISOString(),
        ];
    }
}
