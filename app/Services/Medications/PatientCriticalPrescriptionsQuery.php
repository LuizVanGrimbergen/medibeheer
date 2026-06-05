<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Enums\Medications\MedicationUrgencyTone;
use App\Models\MedicationPrescription;
use App\Models\Patient;
use App\Support\Medications\MedicationUrgencyToneResolver;

final class PatientCriticalPrescriptionsQuery
{
    public function __construct(
        private readonly MedicationUrgencyToneResolver $urgencyToneResolver,
    ) {}

    /**
     * @return list<array{
     *     id: int,
     *     medication_id: int,
     *     medication_name: string,
     *     days_remaining: int,
     *     is_last_in_batch: bool,
     * }>
     */
    public function forPatient(Patient $patient): array
    {
        $activeMedicationIds = $patient->medications()
            ->activeOnMedicationList()
            ->select('medications.id');

        $prescriptions = MedicationPrescription::query()
            ->where('patient_id', '=', $patient->id)
            ->whereNull('completed_at', 'and', false)
            ->whereIn('medication_id', $activeMedicationIds, 'and', false)
            ->with(['medication:id,name'])
            ->orderBy('prescription_expiry_date')
            ->get();

        $urgentPrescriptions = [];

        foreach ($prescriptions as $prescription) {
            $entry = $this->urgentPrescriptionEntry($prescription);

            if ($entry === null) {
                continue;
            }

            $urgentPrescriptions[] = $entry;
        }

        usort(
            $urgentPrescriptions,
            fn (array $left, array $right): int => $left['days_remaining'] <=> $right['days_remaining'],
        );

        return $urgentPrescriptions;
    }

    /**
     * @return array{
     *     id: int,
     *     medication_id: int,
     *     medication_name: string,
     *     days_remaining: int,
     *     is_last_in_batch: bool,
     * }|null
     */
    private function urgentPrescriptionEntry(
        MedicationPrescription $prescription,
    ): ?array {
        $tone = $this->urgencyToneResolver->prescriptionNavAlertToneFor($prescription);

        if ($tone !== MedicationUrgencyTone::CRITICAL) {
            return null;
        }

        $daysRemaining = $this->urgencyToneResolver->prescriptionExpiryDaysRemainingFor(
            $prescription,
        );

        if ($daysRemaining === null) {
            return null;
        }

        return [
            'id' => (int) $prescription->id,
            'medication_id' => (int) $prescription->medication_id,
            'medication_name' => (string) ($prescription->medication?->name ?? ''),
            'days_remaining' => $daysRemaining,
            'is_last_in_batch' => (bool) $prescription->is_last_in_batch,
        ];
    }
}
