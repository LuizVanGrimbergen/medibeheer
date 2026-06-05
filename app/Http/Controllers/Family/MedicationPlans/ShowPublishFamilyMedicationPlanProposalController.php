<?php

namespace App\Http\Controllers\Family\MedicationPlans;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Models\MedicationPlanProposal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowPublishFamilyMedicationPlanProposalController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(
        Request $request,
        MedicationPlanProposal $medicationPlanProposal,
    ): Response {
        $family = $this->authorizeFamilyProfile($request);

        $this->authorizeMedicationPlanProposalForFamily($family, $medicationPlanProposal);
        $this->authorize('publish', $medicationPlanProposal);

        $medicationPlanProposal->load(['items']);

        $itemId = $request->integer('item');
        $itemId = $itemId > 0
            ? $itemId
            : $medicationPlanProposal->items()->orderByDesc('sort_order')->value('id');

        return Inertia::render('Family/MedicationPlans/Publish', [
            'proposal_id' => $medicationPlanProposal->id,
            'cancel_url' => route('family.medication-plans.edit', [
                'medication_plan_proposal' => $medicationPlanProposal,
                'item' => $itemId,
                'summary' => 1,
            ], absolute: false),
        ]);
    }
}
