<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Enums\MedicationPlanProposalStatus;
use App\Models\Family;
use App\Models\MedicationPlanProposal;
use App\Models\MedicationPlanProposalItemSchedule;
use App\Support\BlindIndex;
use Carbon\CarbonInterface;

final class FamilyMedicationPlanProposalsScreenService
{
    public function indexProps(Family $family): array
    {
        $proposals = MedicationPlanProposal::query()
            ->where('family_id', $family->id)
            ->where('status_index', '!=', BlindIndex::forEnum(MedicationPlanProposalStatus::REVOKED))
            ->with(['items', 'patient.user'])
            ->orderByDesc('updated_at')
            ->get();

        return [
            'proposals' => $proposals->map(fn (MedicationPlanProposal $proposal): array => $this->mapProposalSummary($proposal))->values()->all(),
        ];
    }

    public function formProps(MedicationPlanProposal $proposal, ?int $itemId = null): array
    {
        $proposal->load(['items.schedule.weekdays']);

        $item = $itemId !== null
            ? $proposal->items->firstWhere('id', $itemId)
            : $proposal->items->sortByDesc('sort_order')->first();

        if ($item === null) {
            return [
                'proposal_id' => $proposal->id,
                'item_id' => null,
                'initial' => null,
            ];
        }

        $schedule = $item->schedule;

        return [
            'proposal_id' => $proposal->id,
            'item_id' => $item->id,
            'initial' => [
                'name' => $item->name,
                'dose' => $item->dose,
                'dose_unit' => $item->dose_unit?->value,
                'type_medication' => $item->type_medication?->value,
                'strength' => $item->strength,
                'note' => $item->note,
                'current_stock' => $item->current_stock,
                'schedule' => $schedule === null ? null : $this->mapScheduleForForm($schedule),
            ],
        ];
    }

    private function mapProposalSummary(MedicationPlanProposal $proposal): array
    {
        $firstItem = $proposal->items->first();
        $patientName = $proposal->patient?->user?->name;
        $patientEmail = $proposal->invited_patient_email
            ?? $proposal->patient?->user?->email;

        return [
            'id' => $proposal->id,
            'status' => $proposal->status->value,
            'patient_email' => $patientEmail !== null ? (string) $patientEmail : null,
            'patient_name' => $patientName !== null ? (string) $patientName : null,
            'medication_name' => $firstItem?->name,
            'updated_at' => $proposal->updated_at?->toISOString(),
            'can_revoke' => $proposal->isRedeemable(),
            'revoke_url' => route('family.medication-plans.revoke', $proposal, absolute: false),
        ];
    }

    private function mapScheduleForForm(MedicationPlanProposalItemSchedule $schedule): array
    {
        $schedule->loadMissing('weekdays');

        $intakeWeekdays = $schedule->weekdays->isEmpty()
            ? null
            : $schedule->weekdays
                ->pluck('weekday')
                ->map(static fn (mixed $w): int => is_int($w) ? $w : (int) (string) $w)
                ->unique()
                ->sort()
                ->values()
                ->all();

        return [
            'meal_timing' => $schedule->meal_timing?->value,
            'intake_frequency' => $schedule->intake_frequency,
            'times_per_day' => $schedule->times_per_day,
            'dose_time' => $schedule->dose_time,
            'snooze_time' => $schedule->snooze_time,
            'start_date' => $schedule->start_date instanceof CarbonInterface
                ? $schedule->start_date->toDateString()
                : null,
            'end_date' => $schedule->end_date instanceof CarbonInterface
                ? $schedule->end_date->toDateString()
                : null,
            'intake_weekdays' => $intakeWeekdays,
        ];
    }
}
