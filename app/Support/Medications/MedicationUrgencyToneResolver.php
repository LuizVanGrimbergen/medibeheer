<?php

declare(strict_types=1);

namespace App\Support\Medications;

use App\Enums\Medications\MedicationUrgencyTone;
use App\Models\Medication;
use App\Models\MedicationPrescription;
use App\Models\Patient;
use App\Services\Medications\MedicationSupplyEstimateService;
use Carbon\CarbonImmutable;
use DateTimeInterface;

final class MedicationUrgencyToneResolver
{
    public const int CRITICAL_MAX_DAYS = 7;

    public const int REMINDER_URGENT_MAX_DAYS = 2;

    public const int WARNING_MAX_DAYS = 14;

    public function __construct(
        private readonly MedicationSupplyEstimateService $supplyEstimateService,
    ) {}

    public function fromDaysRemaining(int $daysRemaining): MedicationUrgencyTone
    {
        if ($daysRemaining <= self::CRITICAL_MAX_DAYS) {
            return MedicationUrgencyTone::CRITICAL;
        }

        if ($daysRemaining <= self::WARNING_MAX_DAYS) {
            return MedicationUrgencyTone::WARNING;
        }

        return MedicationUrgencyTone::SAFE;
    }

    public function moreUrgent(
        ?MedicationUrgencyTone $current,
        MedicationUrgencyTone $candidate,
    ): MedicationUrgencyTone {
        if ($current === null) {
            return $candidate;
        }

        return $this->rank($candidate) < $this->rank($current) ? $candidate : $current;
    }

    public function navAlertTone(?MedicationUrgencyTone $tone): ?MedicationUrgencyTone
    {
        if ($tone === MedicationUrgencyTone::CRITICAL || $tone === MedicationUrgencyTone::WARNING) {
            return $tone;
        }

        return null;
    }

    public function inventoryNavAlertFor(Patient $patient): ?MedicationUrgencyTone
    {
        $medications = $patient->medications()
            ->activeOnMedicationList()
            ->with(['schedules.weekdays', 'stocks'])
            ->get();

        $worst = null;

        foreach ($medications as $medication) {
            $tone = $this->inventoryToneFor($medication);

            if ($tone === null) {
                continue;
            }

            $worst = $this->moreUrgent($worst, $tone);
        }

        return $this->navAlertTone($worst);
    }

    public function prescriptionNavAlertToneFor(
        MedicationPrescription $prescription,
    ): ?MedicationUrgencyTone {
        return $this->navAlertTone($this->prescriptionExpiryToneFor($prescription));
    }

    public function prescriptionExpiryDaysRemainingFor(
        MedicationPrescription $prescription,
    ): ?int {
        $expiryDate = $prescription->prescription_expiry_date;

        if ($expiryDate === null) {
            return null;
        }

        return $this->daysUntilLocalDate($expiryDate);
    }

    public function prescriptionsNavAlertFor(Patient $patient): ?MedicationUrgencyTone
    {
        $activeMedicationIds = $patient->medications()
            ->activeOnMedicationList()
            ->select('medications.id');

        $prescriptions = MedicationPrescription::query()
            ->where('patient_id', '=', $patient->id)
            ->whereNull('completed_at', 'and', false)
            ->whereIn('medication_id', $activeMedicationIds, 'and', false)
            ->get(['id', 'prescription_expiry_date']);

        $worst = null;

        foreach ($prescriptions as $prescription) {
            $tone = $this->prescriptionExpiryToneFor($prescription);

            if ($tone === null) {
                continue;
            }

            $worst = $this->moreUrgent($worst, $tone);
        }

        return $this->navAlertTone($worst);
    }

    private function inventoryToneFor(Medication $medication): ?MedicationUrgencyTone
    {
        $estimate = $this->supplyEstimateService->estimate($medication);

        if ($estimate['quality'] !== 'approx' || $estimate['days'] === null) {
            return null;
        }

        return $this->fromDaysRemaining((int) $estimate['days']);
    }

    private function prescriptionExpiryToneFor(MedicationPrescription $prescription): ?MedicationUrgencyTone
    {
        $expiryDate = $prescription->prescription_expiry_date;

        if ($expiryDate === null) {
            return null;
        }

        $daysRemaining = $this->daysUntilLocalDate($expiryDate);

        if ($daysRemaining === null) {
            return null;
        }

        return $this->fromDaysRemaining($daysRemaining);
    }

    private function daysUntilLocalDate(DateTimeInterface|string $targetDate): ?int
    {
        $today = MedicationIntakeClock::today()->startOfDay();
        $target = CarbonImmutable::parse($targetDate, MedicationIntakeClock::TIMEZONE)->startOfDay();

        return (int) $today->diffInDays($target, false);
    }

    private function rank(MedicationUrgencyTone $tone): int
    {
        return match ($tone) {
            MedicationUrgencyTone::CRITICAL => 0,
            MedicationUrgencyTone::WARNING => 1,
            MedicationUrgencyTone::SAFE => 2,
        };
    }
}
