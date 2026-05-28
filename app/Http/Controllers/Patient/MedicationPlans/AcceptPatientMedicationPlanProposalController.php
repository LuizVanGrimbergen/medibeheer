<?php

namespace App\Http\Controllers\Patient\MedicationPlans;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Models\MedicationPlanProposal;
use App\Services\Medications\MedicationPlanProposalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcceptPatientMedicationPlanProposalController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        Request $request,
        MedicationPlanProposal $medicationPlanProposal,
        MedicationPlanProposalService $proposalService,
    ): RedirectResponse {
        $this->authorizePatientProfile($request);

        $this->authorize('accept', $medicationPlanProposal);

        $proposalService->acceptProposal($request->user(), $medicationPlanProposal);

        return redirect()
            ->route('patient.medications')
            ->with('success', trans('medication_plan_proposal.flash.accepted'));
    }
}
