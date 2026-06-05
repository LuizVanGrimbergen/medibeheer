<?php

declare(strict_types=1);

namespace App\Services\Medications\PushReminders\LowStock;

use App\Models\Medication;
use App\Services\Medications\MedicationSupplyEstimateService;
use App\Support\Medications\MedicationUrgencyToneResolver;
use App\Support\Medications\PushReminders\PushReminderTier;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class CandidatesQuery
{
    public function __construct(
        private readonly MedicationSupplyEstimateService $supplyEstimateService,
    ) {}

    public function eachCandidate(callable $onCandidate): void
    {
        Medication::query()
            ->activeOnMedicationList()
            ->with(['patient', 'schedules.weekdays', 'stocks'])
            ->chunkById(100, function (EloquentCollection $medications) use ($onCandidate): void {
                foreach ($medications as $medication) {
                    $basePayload = $this->basePayloadFor($medication);

                    if ($basePayload === null) {
                        continue;
                    }

                    $patient = $medication->patient;

                    if ($patient === null) {
                        continue;
                    }

                    foreach (
                        PushReminderTier::forDaysRemaining(
                            $basePayload['supply_estimate_days'],
                        ) as $tier
                    ) {
                        $onCandidate($patient, $medication, [
                            ...$basePayload,
                            'tier' => $tier->value,
                        ]);
                    }
                }
            });
    }

    /**
     * @return array{
     *     medication_id: int,
     *     name: string,
     *     type_medication: string,
     *     supply_estimate_days: int,
     * }|null
     */
    public function basePayloadFor(Medication $medication): ?array
    {
        $estimate = $this->supplyEstimateService->estimate($medication);

        if ($estimate['quality'] !== 'approx' || $estimate['days'] === null) {
            return null;
        }

        if ($estimate['days'] > MedicationUrgencyToneResolver::CRITICAL_MAX_DAYS) {
            return null;
        }

        return [
            'medication_id' => (int) $medication->id,
            'name' => (string) $medication->name,
            'type_medication' => (string) $medication->type_medication->value,
            'supply_estimate_days' => (int) $estimate['days'],
        ];
    }
}
