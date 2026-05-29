<?php

namespace App\Http\Controllers\Patient\MedicationPlans;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Models\MedicationPlanProposal;
use App\Services\Medications\MedicationPlanProposalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeclinePatientMedicationPlanProposalController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        Request $request,
        MedicationPlanProposal $medicationPlanProposal,
        MedicationPlanProposalService $proposalService,
    ): RedirectResponse {
        $this->authorizePatientProfile($request);

        $this->authorize('decline', $medicationPlanProposal);

        $proposalService->declineProposal($request->user(), $medicationPlanProposal);

        return redirect()
            ->route('patient.family')
            ->with('success', trans('medication_plan_proposal.flash.declined'));
    }
}
