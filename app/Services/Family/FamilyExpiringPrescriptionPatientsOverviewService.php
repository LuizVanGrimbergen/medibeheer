<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Enums\Medications\MedicationUrgencyTone;
use App\Models\Family;
use App\Models\MedicationPrescription;
use App\Models\Patient;
use App\Support\Medications\MedicationUrgencyToneResolver;

final class FamilyExpiringPrescriptionPatientsOverviewService
{
    public function __construct(
        private readonly MedicationUrgencyToneResolver $urgencyToneResolver,
    ) {}

    public function forFamily(Family $family): array
    {
        $patients = $family
            ->patients()
            ->with('user')
            ->orderBy('patients.id')
            ->get();

        return $patients
            ->map(fn (Patient $patient): array => $this->patientPayload($patient))
            ->filter(fn (array $payload): bool => $payload['prescriptions'] !== [])
            ->sortBy(fn (array $payload): int => $payload['earliest_days_remaining'] ?? PHP_INT_MAX)
            ->values()
            ->map(function (array $payload): array {
                unset($payload['earliest_days_remaining']);

                return $payload;
            })
            ->all();
    }

    private function patientPayload(Patient $patient): array
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
        $earliestDaysRemaining = null;

        foreach ($prescriptions as $prescription) {
            $entry = $this->urgentPrescriptionEntry($prescription);

            if ($entry === null) {
                continue;
            }

            $urgentPrescriptions[] = $entry;

            $days = $entry['days_remaining'];

            if ($earliestDaysRemaining === null || $days < $earliestDaysRemaining) {
                $earliestDaysRemaining = $days;
            }
        }

        usort(
            $urgentPrescriptions,
            fn (array $left, array $right): int => $left['days_remaining'] <=> $right['days_remaining'],
        );

        $primaryMedicationId = $urgentPrescriptions[0]['medication_id'] ?? null;

        return [
            'patient_id' => (int) $patient->id,
            'patient_name' => (string) ($patient->user?->name ?? 'Patient'),
            'switch_url' => route('family.patients.switch', $patient, absolute: false),
            'medications_url' => route('family.medications', array_filter([
                'medication' => $primaryMedicationId,
            ]), absolute: false),
            'prescriptions' => $urgentPrescriptions,
            'earliest_days_remaining' => $earliestDaysRemaining,
        ];
    }

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
