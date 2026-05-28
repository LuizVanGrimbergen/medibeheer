<?php

declare(strict_types=1);

namespace App\Services\Medications\MedicationPlanProposals;

use App\Enums\MedicationPlanProposalStatus;
use App\Models\Family;
use App\Models\MedicationPlanProposal;
use App\Models\MedicationPlanProposalItem;
use App\Models\MedicationPlanProposalItemSchedule;
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

    public function duplicateAsDraft(Family $family, MedicationPlanProposal $source): MedicationPlanProposal
    {
        return DB::transaction(function () use ($family, $source): MedicationPlanProposal {
            $source->loadMissing(['items.schedule.weekdays']);

            $draft = MedicationPlanProposal::query()->create([
                'patient_id' => null,
                'family_id' => $family->id,
                'status' => MedicationPlanProposalStatus::DRAFT,
            ]);

            foreach ($source->items as $sourceItem) {
                $payload = $this->mapItemToValidatedPayload($sourceItem);
                $this->itemPersistence->createItem($draft, $payload, (int) $sourceItem->sort_order);
            }

            return $draft->fresh(['items.schedule.weekdays']);
        });
    }

    /** @return array<string, mixed> */
    private function mapItemToValidatedPayload(MedicationPlanProposalItem $item): array
    {
        $schedule = $item->schedule;

        $schedulePayload = $schedule instanceof MedicationPlanProposalItemSchedule
            ? [
                'meal_timing' => $schedule->meal_timing?->value,
                'intake_frequency' => $schedule->intake_frequency,
                'times_per_day' => $schedule->times_per_day,
                'dose_time' => $schedule->dose_time,
                'snooze_time' => $schedule->snooze_time,
                'start_date' => $schedule->start_date?->toDateString(),
                'end_date' => $schedule->end_date?->toDateString(),
                'intake_weekdays' => $schedule->weekdays
                    ->pluck('weekday')
                    ->map(static fn (mixed $w): int => is_int($w) ? $w : (int) (string) $w)
                    ->values()
                    ->all(),
            ]
            : [
                'meal_timing' => null,
                'intake_frequency' => null,
                'times_per_day' => null,
                'dose_time' => null,
                'snooze_time' => null,
                'start_date' => null,
                'end_date' => null,
                'intake_weekdays' => null,
            ];

        return [
            'name' => $item->name,
            'dose' => $item->dose,
            'dose_unit' => $item->dose_unit?->value,
            'type_medication' => $item->type_medication?->value,
            'strength' => $item->strength,
            'note' => $item->note,
            'current_stock' => $item->current_stock,
            'schedule' => $schedulePayload,
        ];
    }
}
