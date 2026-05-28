<?php

namespace App\Http\Controllers\Family\MedicationPlans;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Models\MedicationPlanProposal;
use App\Services\Medications\MedicationPlanProposalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DuplicateFamilyMedicationPlanProposalController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __construct(
        private readonly MedicationPlanProposalService $proposalService,
    ) {}

    public function __invoke(Request $request, MedicationPlanProposal $medicationPlanProposal): RedirectResponse
    {
        $family = $this->authorizeFamilyProfile($request);

        $this->authorizeMedicationPlanProposalForFamily($family, $medicationPlanProposal);

        $this->authorize('duplicate', $medicationPlanProposal);

        $draft = $this->proposalService->duplicateAsDraft($family, $medicationPlanProposal);
        $itemId = $draft->items()->orderByDesc('sort_order')->value('id');

        return redirect()->route('family.medication-plans.edit', [
            'medication_plan_proposal' => $draft,
            'item' => $itemId,
            'summary' => 1,
        ]);
    }
}
