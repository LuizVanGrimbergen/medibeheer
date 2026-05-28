<?php

namespace App\Http\Controllers\Patient\MedicationPlans;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Patient\Concerns\AuthorizesPatientProfile;
use App\Models\MedicationPlanProposal;
use App\Services\Medications\MedicationPlanProposalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ShowPatientMedicationPlanProposalReviewController extends Controller
{
    use AuthorizesPatientProfile;

    public function __invoke(
        Request $request,
        MedicationPlanProposal $medicationPlanProposal,
        MedicationPlanProposalService $proposalService,
    ): Response|RedirectResponse {
        $this->authorizePatientProfile($request);

        $this->authorize('accept', $medicationPlanProposal);

        try {
            $props = $proposalService->reviewProps($request->user(), $medicationPlanProposal);
        } catch (ValidationException) {
            return redirect()
                ->route('patient.family')
                ->with('error', trans('medication_plan_proposal.review.unavailable'));
        }

        return Inertia::render('Patient/MedicationPlans/Review', $props);
    }
}
