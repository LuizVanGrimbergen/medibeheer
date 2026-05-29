<?php

namespace App\Http\Controllers\Family\MedicationPlans;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Family\Concerns\AuthorizesFamilyProfile;
use App\Http\Requests\Family\MedicationPlans\StoreFamilyMedicationPlanProposalRequest;
use App\Http\Requests\Family\MedicationPlans\UpdateFamilyMedicationPlanProposalRequest;
use App\Models\MedicationPlanProposal;
use App\Services\Family\FamilyMedicationPlanProposalsScreenService;
use App\Services\Medications\MedicationPlanProposalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class FamilyMedicationPlanProposalController extends Controller
{
    use AuthorizesFamilyProfile;

    public function __construct(
        private readonly FamilyMedicationPlanProposalsScreenService $screenService,
        private readonly MedicationPlanProposalService $proposalService,
    ) {}

    public function create(Request $request): Response
    {
        $this->authorizeFamilyProfile($request);

        $this->authorize('create', MedicationPlanProposal::class);

        return Inertia::render('Family/MedicationPlans/Create');
    }

    public function store(
        StoreFamilyMedicationPlanProposalRequest $request,
    ): RedirectResponse {
        $family = $this->authorizeFamilyProfile($request);

        $this->authorize('create', MedicationPlanProposal::class);

        $proposal = $this->proposalService->createDraft(
            $family,
            $request->validated(),
        );

        $item = $proposal->items()->orderByDesc('sort_order')->first();

        return redirect()
            ->route('family.medication-plans.edit', [
                'medication_plan_proposal' => $proposal,
                'item' => $item?->id,
                'summary' => 1,
            ]);
    }

    public function edit(Request $request, MedicationPlanProposal $medicationPlanProposal): Response
    {
        $family = $this->authorizeFamilyProfile($request);

        $this->authorizeMedicationPlanProposalForFamily($family, $medicationPlanProposal);
        $this->authorize('update', $medicationPlanProposal);

        $itemId = $request->integer('item');
        $itemId = $itemId > 0 ? $itemId : null;

        return Inertia::render('Family/MedicationPlans/Edit', [
            ...$this->screenService->formProps($medicationPlanProposal, $itemId),
            'show_summary' => $request->boolean('summary'),
        ]);
    }

    public function createItem(
        Request $request,
        MedicationPlanProposal $medicationPlanProposal,
    ): Response {
        $family = $this->authorizeFamilyProfile($request);

        $this->authorizeMedicationPlanProposalForFamily($family, $medicationPlanProposal);
        $this->authorize('update', $medicationPlanProposal);

        return Inertia::render('Family/MedicationPlans/AddItem', [
            'proposal_id' => $medicationPlanProposal->id,
        ]);
    }

    public function storeItem(
        StoreFamilyMedicationPlanProposalRequest $request,
        MedicationPlanProposal $medicationPlanProposal,
    ): RedirectResponse {
        $family = $this->authorizeFamilyProfile($request);

        $this->authorizeMedicationPlanProposalForFamily($family, $medicationPlanProposal);
        $this->authorize('update', $medicationPlanProposal);

        $this->proposalService->addDraftItem(
            $medicationPlanProposal,
            $request->validated(),
        );

        $item = $medicationPlanProposal->items()->orderByDesc('sort_order')->first();

        return redirect()
            ->route('family.medication-plans.edit', [
                'medication_plan_proposal' => $medicationPlanProposal,
                'item' => $item?->id,
                'summary' => 1,
            ]);
    }

    public function update(
        UpdateFamilyMedicationPlanProposalRequest $request,
        MedicationPlanProposal $medicationPlanProposal,
    ): RedirectResponse {
        $family = $this->authorizeFamilyProfile($request);

        $this->authorizeMedicationPlanProposalForFamily($family, $medicationPlanProposal);
        $this->authorize('update', $medicationPlanProposal);

        $validated = $request->validated();
        $itemId = isset($validated['item_id']) ? (int) $validated['item_id'] : null;

        $this->proposalService->updateDraft(
            $medicationPlanProposal,
            Arr::except($validated, ['item_id']),
            $itemId > 0 ? $itemId : null,
        );

        return redirect()
            ->route('family.medication-plans.edit', [
                'medication_plan_proposal' => $medicationPlanProposal,
                'item' => $itemId > 0 ? $itemId : $medicationPlanProposal->items()->orderByDesc('sort_order')->value('id'),
                'summary' => 1,
            ]);
    }
}
