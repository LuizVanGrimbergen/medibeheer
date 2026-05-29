<?php

namespace App\Http\Requests\Family\MedicationPlans;

use Illuminate\Validation\Rule;

class UpdateFamilyMedicationPlanProposalRequest extends StoreFamilyMedicationPlanProposalRequest
{
    public function rules(): array
    {
        $proposal = $this->route('medication_plan_proposal');

        return [
            ...parent::rules(),
            'item_id' => [
                'nullable',
                'integer',
                Rule::exists('medication_plan_proposal_items', 'id')
                    ->where('medication_plan_proposal_id', $proposal?->id ?? 0),
            ],
        ];
    }
}
