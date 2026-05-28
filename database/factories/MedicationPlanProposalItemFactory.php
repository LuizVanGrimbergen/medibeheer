<?php

namespace Database\Factories;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationType;
use App\Models\MedicationPlanProposal;
use App\Models\MedicationPlanProposalItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicationPlanProposalItem>
 */
class MedicationPlanProposalItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'medication_plan_proposal_id' => MedicationPlanProposal::factory(),
            'sort_order' => 0,
            'name' => fake()->words(2, true),
            'dose' => '1',
            'dose_unit' => MedicationDoseUnit::PIECE,
            'type_medication' => MedicationType::PILL,
            'strength' => null,
            'note' => null,
            'current_stock' => '20',
        ];
    }
}
