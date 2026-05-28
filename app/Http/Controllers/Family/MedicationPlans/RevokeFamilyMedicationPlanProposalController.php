<?php

namespace App\Http\Controllers\Family\MedicationPlans;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Models\MedicationPlanProposal;
use App\Services\Medications\MedicationPlanProposalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RevokeFamilyMedicationPlanProposalController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(
        Request $request,
        MedicationPlanProposal $medicationPlanProposal,
        MedicationPlanProposalService $proposalService,
    ): RedirectResponse {
        $family = $this->authorizeFamilyProfile($request);

        $this->authorizeMedicationPlanProposalForFamily($family, $medicationPlanProposal);

        $this->authorize('revoke', $medicationPlanProposal);

        $proposalService->revoke($medicationPlanProposal, $request->user());

        return redirect()
            ->route('family.link')
            ->with('success', trans('medication_plan_proposal.flash.revoked'));
    }
}
