<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Enums\MedicationIntakeDayPeriod;
use App\Enums\MedicationIntakeDayStatus;
use App\Models\Medication;
use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Models\Patient;
use App\Support\Medications\DoseTime;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationScheduleDoseTimes;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final class PatientScheduledIntakesQuery
{
    public function __construct(
        private readonly MedicationScheduleOccursOnDate $scheduleOccursOnDate,
        private readonly MedicationSupplyEstimateService $supplyEstimateService,
    ) {}

    public function forPatientOnDate(Patient $patient, ?CarbonInterface $date = null): array
    {
        $targetDate = $this->resolveTargetDate($date);

        $medications = $this->loadMedicationsFor($patient);
        $intakes = $this->loadIntakesByKey($patient, $targetDate);
        $supplyEstimates = $this->buildSupplyEstimates($medications, $targetDate);

        return $this->buildSlotsForDate($medications, $targetDate, $intakes, $supplyEstimates);
    }

    public function monthCalendarDataForPatient(Patient $patient, string $calendarMonth): array
    {
        $monthStart = CarbonImmutable::createFromFormat('Y-m', $calendarMonth)->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();
        $medications = $this->loadMedicationsFor($patient);

        $intakesInMonth = MedicationIntake::query()
            ->where('patient_id', $patient->id)
            ->whereBetween('intake_date', [$monthStart->toDateString(), $monthEnd->toDateString()], 'and')
            ->get();

        $intakesByDate = $intakesInMonth->groupBy(
            fn (MedicationIntake $intake): string => $intake->intake_date->toDateString(),
        );

        $supplyEstimates = $this->buildSupplyEstimates($medications, MedicationIntakeClock::today());

        $days = [];
        $slots = [];

        for ($date = $monthStart; $date <= $monthEnd; $date = $date->addDay()) {
            $dateKey = $date->toDateString();
            $dayIntakes = ($intakesByDate->get($dateKey) ?? collect())
                ->keyBy(
                    fn (MedicationIntake $intake): string => $this->intakeKey(
                        $intake->medication_schedule_id,
                        (string) $intake->dose_time,
                    ),
                );
            $daySlots = $this->buildSlotsForDate($medications, $date, $dayIntakes, $supplyEstimates);
            $scheduledCount = count($daySlots);
            $takenCount = count(array_filter(
                $daySlots,
                static fn (array $slot): bool => $slot['taken_at'] !== null,
            ));

            $days[] = [
                'date' => $dateKey,
                'status' => MedicationIntakeDayStatus::fromCounts($scheduledCount, $takenCount)->value,
                'scheduled_count' => $scheduledCount,
                'taken_count' => $takenCount,
            ];

            foreach ($daySlots as $slot) {
                $slots[] = [
                    ...$slot,
                    'intake_date' => $dateKey,
                ];
            }
        }

        return [
            'days' => $days,
            'slots' => $slots,
        ];
    }

    private function buildSlotsForDate(
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

    private function loadMedicationsFor(Patient $patient): EloquentCollection
    {
        return $patient->medications()
            ->with([
                'schedules' => fn ($query) => $query->orderBy('id')->with('weekdays'),
                'stocks',
            ])
            ->orderBy('name')
            ->get();
    }

    private function loadIntakesByKey(Patient $patient, CarbonImmutable $date): EloquentCollection
    {
        return MedicationIntake::query()
            ->where('patient_id', $patient->id)
            ->whereDate('intake_date', '=', $date->toDateString(), 'and')
            ->get()
            ->keyBy(fn (MedicationIntake $intake): string => $this->intakeKey(
                $intake->medication_schedule_id,
                (string) $intake->dose_time,
            ));
    }

    private function buildSupplyEstimates(EloquentCollection $medications, CarbonImmutable $date): array
    {
        $estimates = [];

        foreach ($medications as $medication) {
            $estimates[$medication->id] = $this->supplyEstimateService->estimate($medication, $date);
        }

        return $estimates;
    }

    private function buildIntakePayload(
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
            ),
            'intake_window_state' => MedicationScheduleDoseTimes::resolveIntakeWindowState(
                $doseTime,
                (string) $schedule->dose_time,
            ),
            'day_period' => $dayPeriod->value,
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

    private function compareScheduledIntakes(array $left, array $right): int
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

    private function doseTimeMinutes(string $value): int
    {
        return DoseTime::tryFrom($value)?->minutesSinceMidnight() ?? 24 * 60;
    }

    private function resolveTargetDate(?CarbonInterface $date): CarbonImmutable
    {
        if ($date === null) {
            return MedicationIntakeClock::today();
        }

        return CarbonImmutable::parse($date)->startOfDay();
    }

    private function intakeKey(int $scheduleId, string $doseTime): string
    {
        return "{$scheduleId}|".trim($doseTime);
    }
}
