<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Enums\MedicationIntakeDayPeriod;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Support\Medications\DoseTime;
use App\Support\Medications\MedicationScheduleDoseTimes;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final class PatientScheduledIntakeSlotBuilder
{
    public function __construct(
        private readonly MedicationScheduleOccursOnDate $scheduleOccursOnDate,
        private readonly MedicationSupplyEstimateService $supplyEstimateService,
    ) {}

    public function buildSlotsForDate(
        EloquentCollection $medications,
        CarbonImmutable $date,
        Collection $intakes,
        array $supplyEstimates,
    ): array {
        $scheduled = [];

        foreach ($medications as $medication) {
            foreach ($medication->schedules as $schedule) {
                if (! $this->scheduleOccursOnDate->isIntakeDueOn($schedule, $date)) {
                    continue;
                }

                foreach ($this->scheduleOccursOnDate->sortedDoseTimes($schedule) as $doseTime) {
                    if ($doseTime === '') {
                        continue;
                    }

                    $scheduled[] = $this->buildIntakePayload(
                        $medication,
                        $schedule,
                        $doseTime,
                        $intakes,
                        $supplyEstimates,
                    );
                }
            }
        }

        usort($scheduled, $this->compareScheduledIntakes(...));

        return $scheduled;
    }

    public function buildSupplyEstimates(EloquentCollection $medications, CarbonImmutable $date): array
    {
        $estimates = [];

        foreach ($medications as $medication) {
            $estimates[$medication->id] = $this->supplyEstimateService->estimate($medication, $date);
        }

        return $estimates;
    }

    public function compareScheduledIntakes(array $left, array $right): int
    {
        $leftPeriod = MedicationIntakeDayPeriod::tryFrom((string) $left['day_period']);
        $rightPeriod = MedicationIntakeDayPeriod::tryFrom((string) $right['day_period']);

        $periodCompare = ($leftPeriod?->sortRank() ?? PHP_INT_MAX)
            <=> ($rightPeriod?->sortRank() ?? PHP_INT_MAX);

        if ($periodCompare !== 0) {
            return $periodCompare;
        }

        $leftTaken = $left['taken_at'] !== null;
        $rightTaken = $right['taken_at'] !== null;

        if ($leftTaken !== $rightTaken) {
            return $leftTaken <=> $rightTaken;
        }

        return $this->doseTimeMinutes((string) ($left['dose_time'] ?? ''))
            <=> $this->doseTimeMinutes((string) ($right['dose_time'] ?? ''));
    }

    public function intakeKey(int $scheduleId, string $doseTime): string
    {
        return "{$scheduleId}|".trim($doseTime);
    }

    public function buildReminderSlot(
        Medication $medication,
        MedicationSchedule $schedule,
        string $doseTime,
    ): array {
        return [
            'medication_id' => $medication->id,
            'medication_schedule_id' => $schedule->id,
            'dose_time' => $doseTime,
            'name' => (string) $medication->name,
            'type_medication' => $medication->type_medication->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildIntakePayload(
        Medication $medication,
        MedicationSchedule $schedule,
        string $doseTime,
        Collection $intakes,
        array $supplyEstimates,
    ): array {
        $intake = $intakes->get($this->intakeKey($schedule->id, $doseTime));
        $dayPeriod = MedicationIntakeDayPeriod::fromDoseTime($doseTime);
        $supplyEstimate = $supplyEstimates[$medication->id];

        return [
            'medication_id' => $medication->id,
            'medication_schedule_id' => $schedule->id,
            'dose_time' => $doseTime,
            'snooze_minutes' => MedicationScheduleDoseTimes::snoozeMinutesFor(
                $doseTime,
                (string) $schedule->dose_time,
                $schedule->snooze_time,
            ),
            'intake_window_state' => MedicationScheduleDoseTimes::resolveIntakeWindowState(
                $doseTime,
                (string) $schedule->dose_time,
                $schedule->snooze_time,
            ),
            'day_period' => $dayPeriod->value,
            'meal_timing' => $schedule->meal_timing->value,
            'intake_frequency' => $schedule->intake_frequency,
            'intake_weekdays' => $schedule->intake_weekdays,
            'name' => (string) $medication->name,
            'type_medication' => $medication->type_medication->value,
            'dose' => $this->firstFilledDose($schedule, $medication),
            'dose_unit' => $medication->dose_unit?->value,
            'note' => filled($medication->note) ? (string) $medication->note : null,
            'taken_at' => $intake?->taken_at?->toIso8601String(),
            'stocks' => $medication->stocks
                ->map(static fn ($stock): array => [
                    'current_stock' => (string) $stock->current_stock,
                ])
                ->values()
                ->all(),
            'supply_estimate_days' => $supplyEstimate['days'],
            'supply_estimate_quality' => $supplyEstimate['quality'],
        ];
    }

    private function firstFilledDose(MedicationSchedule $schedule, Medication $medication): ?string
    {
        foreach ([$schedule->dose_quantity, $medication->dose] as $candidate) {
            $value = trim((string) $candidate);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function doseTimeMinutes(string $value): int
    {
        return DoseTime::tryFrom($value)?->minutesSinceMidnight() ?? 24 * 60;
    }
}
