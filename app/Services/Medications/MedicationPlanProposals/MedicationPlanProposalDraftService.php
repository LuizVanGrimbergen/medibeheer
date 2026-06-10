<?php

declare(strict_types=1);

namespace App\Services\Medications\MedicationPlanProposals;

use App\Enums\MedicationPlanProposalStatus;
use App\Models\Family;
use App\Models\MedicationPlanProposal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class MedicationPlanProposalDraftService
{
    public function __construct(
        private readonly MedicationPlanProposalItemPersistence $itemPersistence,
    ) {}

    /** @param array<string, mixed> $validated */
    public function createDraft(Family $family, array $validated): MedicationPlanProposal
    {
        return DB::transaction(function () use ($family, $validated): MedicationPlanProposal {
            $proposal = MedicationPlanProposal::query()->create([
                'patient_id' => null,
                'family_id' => $family->id,
                'status' => MedicationPlanProposalStatus::DRAFT,
            ]);

            $this->itemPersistence->createItem($proposal, $validated, 0);

            return $proposal->load(['items.schedule.weekdays']);
        });
    }

    /** @param array<string, mixed> $validated */
    public function addDraftItem(MedicationPlanProposal $proposal, array $validated): MedicationPlanProposal
    {
        if (! $proposal->isDraft()) {
            throw ValidationException::withMessages([
                'proposal' => [trans('medication_plan_proposal.publish.not_draft')],
            ]);
        }

        return DB::transaction(function () use ($proposal, $validated): MedicationPlanProposal {
            $nextSortOrder = ((int) $proposal->items()->max('sort_order')) + 1;

            $this->itemPersistence->createItem($proposal, $validated, $nextSortOrder);

            return $proposal->fresh(['items.schedule.weekdays']);
        });
    }

    /** @param array<string, mixed> $validated */
    public function updateDraft(
        MedicationPlanProposal $proposal,
        array $validated,
        ?int $itemId = null,
    ): MedicationPlanProposal {
        return DB::transaction(function () use ($proposal, $validated, $itemId): MedicationPlanProposal {
            $itemQuery = $proposal->items();

            if ($itemId !== null) {
                $itemQuery->whereKey($itemId);
            }

            $item = $itemQuery->orderByDesc('sort_order')->firstOrFail();

            $this->itemPersistence->updateItem($item, $validated);

            return $proposal->fresh(['items.schedule.weekdays']);
        });
    }
}
