<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\Family;
use App\Models\Patient;
use App\Services\Medications\PatientCriticalPrescriptionsQuery;

final class FamilyExpiringPrescriptionPatientsOverviewService
{
    public function __construct(
        private readonly PatientCriticalPrescriptionsQuery $criticalPrescriptionsQuery,
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
        $urgentPrescriptions = $this->criticalPrescriptionsQuery->forPatient($patient);
        $earliestDaysRemaining = $urgentPrescriptions[0]['days_remaining'] ?? null;
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
}
