<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Enums\MedicationIntakeDayStatus;
use App\Models\MedicationIntake;
use App\Models\Patient;
use App\Support\Medications\MedicationIntakeClock;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final class PatientScheduledIntakesQuery
{
    public function __construct(
        private readonly PatientScheduledIntakeSlotBuilder $slotBuilder,
    ) {}

    public function forPatientOnDate(Patient $patient, ?CarbonInterface $date = null): array
    {
        $targetDate = $this->resolveTargetDate($date);

        $medications = $this->loadMedicationsFor($patient);
        $intakes = $this->loadIntakesByKey($patient, $targetDate);
        $supplyEstimates = $this->slotBuilder->buildSupplyEstimates($medications, $targetDate);

        return $this->slotBuilder->buildSlotsForDate($medications, $targetDate, $intakes, $supplyEstimates);
    }

    public function monthCalendarDataForPatient(Patient $patient, string $calendarMonth): array
    {
        $monthStart = CarbonImmutable::createFromFormat('Y-m', $calendarMonth)->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();
        $context = $this->buildDateRangeContext($patient, $monthStart, $monthEnd);

        $days = [];
        $slots = [];

        foreach ($this->datesBetween($monthStart, $monthEnd) as $date) {
            $dateKey = $date->toDateString();
            $daySlots = $this->slotBuilder->buildSlotsForDate(
                $context['medications'],
                $date,
                $this->intakesKeyedForDate($context['intakes_by_date'], $dateKey),
                $context['supply_estimates'],
            );
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

            $this->appendDatedSlots($slots, $daySlots, $dateKey);
        }

        return [
            'days' => $days,
            'slots' => $slots,
        ];
    }

    public function takenSlotsWithinDaysForPatient(Patient $patient, int $days): array
    {
        $today = MedicationIntakeClock::today();
        $from = $today->subDays($days - 1);

        return $this->takenSlotsForPatientBetweenDates($patient, $from, $today);
    }

    public function slotsForPatientBetweenDates(
        Patient $patient,
        CarbonImmutable $from,
        CarbonImmutable $to,
    ): array {
        $slots = $this->collectDatedSlotsBetween($patient, $from, $to);

        $this->sortSlotsByDateDesc($slots);

        return $slots;
    }

    public function takenSlotsForPatientBetweenDates(
        Patient $patient,
        CarbonImmutable $from,
        CarbonImmutable $to,
    ): array {
        return array_values(array_filter(
            $this->slotsForPatientBetweenDates($patient, $from, $to),
            static fn (array $slot): bool => $slot['taken_at'] !== null,
        ));
    }

    private function datesBetween(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $dates = [];

        for ($date = $from; $date <= $to; $date = $date->addDay()) {
            $dates[] = $date;
        }

        return $dates;
    }

    private function buildDateRangeContext(
        Patient $patient,
        CarbonImmutable $from,
        CarbonImmutable $to,
    ): array {
        $medications = $this->loadMedicationsFor($patient);

        return [
            'medications' => $medications,
            'intakes_by_date' => $this->loadIntakesGroupedByDate($patient, $from, $to),
            'supply_estimates' => $this->slotBuilder->buildSupplyEstimates($medications, MedicationIntakeClock::today()),
        ];
    }

    private function loadIntakesGroupedByDate(
        Patient $patient,
        CarbonImmutable $from,
        CarbonImmutable $to,
    ): Collection {
        return MedicationIntake::query()
            ->where('patient_id', $patient->id)
            ->whereBetween('intake_date', [$from->toDateString(), $to->toDateString()], 'and')
            ->get()
            ->groupBy(
                fn (MedicationIntake $intake): string => $intake->intake_date->toDateString(),
            );
    }

    private function intakesKeyedForDate(Collection $intakesByDate, string $dateKey): Collection
    {
        return ($intakesByDate->get($dateKey) ?? collect())
            ->keyBy(
                fn (MedicationIntake $intake): string => $this->slotBuilder->intakeKey(
                    $intake->medication_schedule_id,
                    (string) $intake->dose_time,
                ),
            );
    }

    private function appendDatedSlots(array &$slots, array $daySlots, string $dateKey): void
    {
        foreach ($daySlots as $slot) {
            $slots[] = [
                ...$slot,
                'intake_date' => $dateKey,
            ];
        }
    }

    private function collectDatedSlotsBetween(
        Patient $patient,
        CarbonImmutable $from,
        CarbonImmutable $to,
    ): array {
        $context = $this->buildDateRangeContext($patient, $from, $to);
        $slots = [];

        foreach ($this->datesBetween($from, $to) as $date) {
            $dateKey = $date->toDateString();
            $daySlots = $this->slotBuilder->buildSlotsForDate(
                $context['medications'],
                $date,
                $this->intakesKeyedForDate($context['intakes_by_date'], $dateKey),
                $context['supply_estimates'],
            );

            $this->appendDatedSlots($slots, $daySlots, $dateKey);
        }

        return $slots;
    }

    private function sortSlotsByDateDesc(array &$slots): void
    {
        usort($slots, function (array $left, array $right): int {
            $dateComparison = strcmp($right['intake_date'], $left['intake_date']);

            if ($dateComparison !== 0) {
                return $dateComparison;
            }

            return $this->slotBuilder->compareScheduledIntakes($left, $right);
        });
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

    private function loadIntakesByKey(Patient $patient, CarbonImmutable $date): Collection
    {
        return MedicationIntake::query()
            ->where('patient_id', $patient->id)
            ->whereDate('intake_date', '=', $date->toDateString(), 'and')
            ->get()
            ->keyBy(fn (MedicationIntake $intake): string => $this->slotBuilder->intakeKey(
                $intake->medication_schedule_id,
                (string) $intake->dose_time,
            ));
    }

    private function resolveTargetDate(?CarbonInterface $date): CarbonImmutable
    {
        if ($date === null) {
            return MedicationIntakeClock::today();
        }

        return CarbonImmutable::parse($date)->startOfDay();
    }
}
