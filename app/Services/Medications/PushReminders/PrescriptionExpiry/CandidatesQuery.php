<?php

declare(strict_types=1);

namespace App\Services\Medications\PushReminders\PrescriptionExpiry;

use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Support\Medications\MedicationUrgencyToneResolver;
use App\Support\Medications\PushReminders\PushReminderTier;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class CandidatesQuery
{
    public function __construct(
        private readonly MedicationUrgencyToneResolver $urgencyToneResolver,
    ) {}

    public function eachCandidate(callable $onCandidate): void
    {
        $activeMedicationIds = Medication::query()
            ->activeOnMedicationList()
            ->select('medications.id');

        MedicationPrescription::query()
            ->whereNull('completed_at', 'and', false)
            ->whereIn('medication_id', $activeMedicationIds, 'and', false)
            ->with(['medication', 'patient'])
            ->chunkById(100, function (EloquentCollection $prescriptions) use ($onCandidate): void {
                foreach ($prescriptions as $prescription) {
                    $basePayload = $this->basePayloadFor($prescription);

                    if ($basePayload === null) {
                        continue;
                    }

                    $patient = $prescription->patient;
                    $medication = $prescription->medication;

                    if ($patient === null || $medication === null) {
                        continue;
                    }

                    foreach (
                        PushReminderTier::forDaysRemaining(
                            $basePayload['days_remaining'],
                        ) as $tier
                    ) {
                        $onCandidate($patient, $prescription, $medication, [
                            ...$basePayload,
                            'tier' => $tier->value,
                        ]);
                    }
                }
            });
    }

    /**
     * @return array{
     *     prescription_id: int,
     *     medication_id: int,
     *     name: string,
     *     type_medication: string,
     *     days_remaining: int,
     *     is_last_in_batch: bool,
     * }|null
     */
    public function basePayloadFor(MedicationPrescription $prescription): ?array
    {
        if ($prescription->prescription_expiry_date === null) {
            return null;
        }

        $daysRemaining = $this->urgencyToneResolver->prescriptionExpiryDaysRemainingFor(
            $prescription,
        );

        if ($daysRemaining === null || $daysRemaining > MedicationUrgencyToneResolver::CRITICAL_MAX_DAYS) {
            return null;
        }

        return [
            'prescription_id' => (int) $prescription->id,
            'medication_id' => (int) $prescription->medication_id,
            'name' => (string) ($prescription->medication?->name ?? ''),
            'type_medication' => (string) ($prescription->medication?->type_medication->value ?? ''),
            'days_remaining' => $daysRemaining,
            'is_last_in_batch' => (bool) $prescription->is_last_in_batch,
        ];
    }
}
