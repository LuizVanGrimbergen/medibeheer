<?php

declare(strict_types=1);

namespace App\Services\Medications;

use App\Models\Medication;
use App\Models\Patient;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use App\Support\Medications\MedicationStockNumericParser;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class MedicationVacationSupplyService
{
    public function __construct(
        private readonly MedicationScheduleDailyConsumption $scheduleDailyConsumption,
        private readonly MedicationScheduleOccursOnDate $scheduleOccursOnDate,
        private readonly MedicationStockNumericParser $stockNumericParser,
    ) {}

    /**
     * @return array{
     *     vacation_days: int,
     *     items: list<array{
     *         medication_id: int,
     *         name: string,
     *         type_medication: string,
     *         dose_unit: string|null,
     *         pickup_quantity: string,
     *         needed_for_period: string,
     *         current_stock: string,
     *         stock_pieces_per_package: int|null,
     *     }>,
     *     skipped_medication_count: int,
     * }
     */
    public function buildPickupList(
        Patient $patient,
        CarbonInterface $startsOn,
        CarbonInterface $endsOn,
    ): array {
        $startDay = CarbonImmutable::parse($startsOn)->startOfDay();
        $endDay = CarbonImmutable::parse($endsOn)->startOfDay();

        $medications = $patient->medications()
            ->activeOnMedicationList()
            ->with(['schedules.weekdays', 'stocks'])
            ->orderBy('name')
            ->get();

        $items = [];
        $skippedMedicationCount = 0;

        foreach ($medications as $medication) {
            $line = $this->pickupLineForMedication($medication, $startDay, $endDay);

            if ($line === null) {
                $skippedMedicationCount++;

                continue;
            }

            if ($line['pickup_quantity_numeric'] <= 0.0) {
                continue;
            }

            $items[] = [
                'medication_id' => $medication->id,
                'name' => (string) $medication->name,
                'type_medication' => $medication->type_medication->value,
                'dose_unit' => $medication->dose_unit?->value,
                'pickup_quantity' => $this->formatQuantity($line['pickup_quantity_numeric']),
                'needed_for_period' => $this->formatQuantity($line['needed_for_period_numeric']),
                'current_stock' => (string) $medication->stocks->first()?->current_stock ?? '',
                'stock_pieces_per_package' => filled($medication->stock_pieces_per_package)
                    ? (int) $medication->stock_pieces_per_package
                    : null,
            ];
        }

        return [
            'vacation_days' => (int) ($startDay->diffInDays($endDay) + 1),
            'items' => $items,
            'skipped_medication_count' => $skippedMedicationCount,
        ];
    }

    /**
     * @return array{
     *     needed_for_period_numeric: float,
     *     pickup_quantity_numeric: float,
     * }|null
     */
    private function pickupLineForMedication(
        Medication $medication,
        CarbonImmutable $startDay,
        CarbonImmutable $endDay,
    ): ?array {
        $stock = $medication->stocks->first();

        if ($stock === null || $medication->schedules->isEmpty()) {
            return null;
        }

        $currentStock = $this->stockNumericParser->parse(
            (string) $stock->current_stock,
            $medication->dose_unit,
        );

        if ($currentStock === null) {
            return null;
        }

        $neededForPeriod = $this->neededUnitsForPeriod($medication->schedules, $startDay, $endDay);

        if ($neededForPeriod === null || $neededForPeriod <= 0.0) {
            return null;
        }

        $pickupQuantity = max(0.0, $neededForPeriod - $currentStock);

        return [
            'needed_for_period_numeric' => $neededForPeriod,
            'pickup_quantity_numeric' => $pickupQuantity,
        ];
    }

    private function neededUnitsForPeriod(
        EloquentCollection $schedules,
        CarbonImmutable $startDay,
        CarbonImmutable $endDay,
    ): ?float {
        $total = 0.0;
        $hasSchedulableConsumption = false;

        foreach (CarbonPeriod::create($startDay, $endDay) as $date) {
            $dayTotal = 0.0;
            $dayHasConsumption = false;

            foreach ($schedules as $schedule) {
                if (! $this->scheduleOccursOnDate->isActiveOn($schedule, $date)) {
                    continue;
                }

                $units = $this->scheduleDailyConsumption->unitsWhenIntakeDueOn($schedule, $date);

                if ($units === null) {
                    continue;
                }

                $dayTotal += $units;
                $dayHasConsumption = true;
            }

            if ($dayHasConsumption) {
                $hasSchedulableConsumption = true;
                $total += $dayTotal;
            }
        }

        if (! $hasSchedulableConsumption) {
            return null;
        }

        return $total;
    }

    private function formatQuantity(float $value): string
    {
        $rounded = round($value, 4);

        if (abs($rounded - round($rounded)) < 0.0001) {
            return (string) (int) round($rounded);
        }

        $formatted = rtrim(rtrim(number_format($rounded, 2, '.', ''), '0'), '.');

        return str_replace('.', ',', $formatted);
    }
}
