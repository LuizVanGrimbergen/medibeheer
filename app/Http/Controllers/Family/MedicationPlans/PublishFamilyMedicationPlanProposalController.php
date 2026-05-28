<?php

namespace App\Http\Controllers\Family\MedicationPlans;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Http\Requests\Family\MedicationPlans\PublishFamilyMedicationPlanProposalRequest;
use App\Models\MedicationPlanProposal;
use App\Services\Medications\MedicationPlanProposalService;
use Illuminate\Http\RedirectResponse;

class PublishFamilyMedicationPlanProposalController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __invoke(
        PublishFamilyMedicationPlanProposalRequest $request,
        MedicationPlanProposal $medicationPlanProposal,
        MedicationPlanProposalService $proposalService,
    ): RedirectResponse {
        $family = $this->authorizeFamilyProfile($request);

        $this->authorizeMedicationPlanProposalForFamily($family, $medicationPlanProposal);

        $this->authorize('publish', $medicationPlanProposal);

        $proposalService->publish(
            $medicationPlanProposal,
            $request->user(),
            $request->normalizedPatientEmail(),
        );

        return redirect()
            ->route('family.link')
            ->with('success', trans('medication_plan_proposal.flash.mail_sent'));
    }
}
