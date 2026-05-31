<?php

declare(strict_types=1);

namespace App\Services\Family;

use App\Models\Family;
use App\Models\Medication;
use App\Models\Patient;
use App\Services\Medications\MedicationSupplyEstimateService;

final class FamilyLowStockPatientsOverviewService
{
    private const int CRITICAL_MAX_SUPPLY_DAYS = 7;

    public function __construct(
        private readonly MedicationSupplyEstimateService $supplyEstimateService,
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
            ->filter(fn (array $payload): bool => $payload['medications'] !== [])
            ->sortBy(fn (array $payload): int => $payload['lowest_supply_estimate_days'] ?? PHP_INT_MAX)
            ->values()
            ->map(function (array $payload): array {
                unset($payload['lowest_supply_estimate_days']);

                return $payload;
            })
            ->all();
    }

    private function patientPayload(Patient $patient): array
    {
        $medications = $patient
            ->medications()
            ->activeOnMedicationList()
            ->with(['schedules.weekdays', 'stocks'])
            ->orderBy('name')
            ->get();

        $criticalMedications = [];
        $lowestSupplyEstimateDays = null;

        foreach ($medications as $medication) {
            $entry = $this->criticalMedicationEntry($medication);

            if ($entry === null) {
                continue;
            }

            $criticalMedications[] = $entry;

            $days = $entry['supply_estimate_days'];

            if ($lowestSupplyEstimateDays === null || $days < $lowestSupplyEstimateDays) {
                $lowestSupplyEstimateDays = $days;
            }
        }

        usort(
            $criticalMedications,
            fn (array $left, array $right): int => $left['supply_estimate_days'] <=> $right['supply_estimate_days'],
        );

        $name = $patient->user?->name ?? 'Patient';

        $primaryMedicationId = $criticalMedications[0]['id'] ?? null;

        return [
            'patient_id' => (int) $patient->id,
            'patient_name' => (string) $name,
            'switch_url' => route('family.patients.switch', $patient, absolute: false),
            'medications_url' => route('family.medications', array_filter([
                'medication' => $primaryMedicationId,
            ]), absolute: false),
            'medications' => $criticalMedications,
            'lowest_supply_estimate_days' => $lowestSupplyEstimateDays,
        ];
    }

    private function criticalMedicationEntry(Medication $medication): ?array
    {
        $estimate = $this->supplyEstimateService->estimate($medication);

        if ($estimate['quality'] !== 'approx' || $estimate['days'] === null) {
            return null;
        }

        if ($estimate['days'] > self::CRITICAL_MAX_SUPPLY_DAYS) {
            return null;
        }

        return [
            'id' => (int) $medication->id,
            'name' => (string) $medication->name,
            'supply_estimate_days' => (int) $estimate['days'],
        ];
    }
}
