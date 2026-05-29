<?php

namespace App\Http\Resources\Patient;

use App\Models\MedicationPlanProposal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MedicationPlanProposal */
class PatientPendingMedicationPlanProposalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $firstItem = $this->items->first();

        return [
            'id' => $this->id,
            'medication_name' => $firstItem?->name,
            'family_member_name' => (string) ($this->family?->user?->name ?? ''),
            'expires_at' => $this->expires_at?->toISOString(),
            'accept_url' => route('patient.medication-plans.accept', $this->resource, absolute: false),
            'decline_url' => route('patient.medication-plans.decline', $this->resource, absolute: false),
            'review_url' => route('patient.medication-plans.review', $this->resource, absolute: false),
        ];
    }
}
