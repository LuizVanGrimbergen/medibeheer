<?php

declare(strict_types=1);

namespace App\Services\Medications\MedicationPlanProposals;

use App\Models\MedicationPlanProposal;
use App\Models\MedicationPlanProposalItem;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Support\Arr;

final class MedicationPlanProposalItemPersistence
{
    public function createItem(MedicationPlanProposal $proposal, array $validated, int $sortOrder): void
    {
        $payload = $this->normalizeValidatedPayload($validated);

        $item = $proposal->items()->create([
            ...$payload['medication'],
            'sort_order' => $sortOrder,
            'current_stock' => $payload['stock'],
        ]);

        $this->syncSchedule($item, $payload['schedule'], $payload['intake_weekdays']);
    }

    public function updateItem(MedicationPlanProposalItem $item, array $validated): void
    {
        $payload = $this->normalizeValidatedPayload($validated);

        $item->fill([
            ...$payload['medication'],
            'current_stock' => $payload['stock'],
        ])->save();

        $schedule = $item->schedule;

        if ($schedule === null) {
            $schedule = $item->schedule()->create($payload['schedule']);
        } else {
            $schedule->fill($payload['schedule'])->save();
        }

        $schedule->syncIntakeWeekdays($payload['intake_weekdays']);
    }

    private function normalizeValidatedPayload(array $validated): array
    {
        $normalizedSchedule = MedicationScheduleIntakeWeekdays::normalizeNestedSchedule($validated['schedule']);

        return [
            'medication' => Arr::except($validated, [
                'schedule',
                'current_stock',
            ]),
            'schedule' => Arr::except($normalizedSchedule, ['intake_weekdays']),
            'intake_weekdays' => $normalizedSchedule['intake_weekdays'],
            'stock' => $validated['current_stock'] ?? null,
        ];
    }

    private function syncSchedule(
        MedicationPlanProposalItem $item,
        array $scheduleAttributes,
        ?array $intakeWeekdays,
    ): void {
        $schedule = $item->schedule()->create($scheduleAttributes);
        $schedule->syncIntakeWeekdays($intakeWeekdays);
    }
}
