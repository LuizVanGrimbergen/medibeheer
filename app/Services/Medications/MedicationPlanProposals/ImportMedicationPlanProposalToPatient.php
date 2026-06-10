<?php

declare(strict_types=1);

namespace App\Services\Medications\MedicationPlanProposals;

use App\Models\MedicationPlanProposal;
use App\Models\Patient;

final class ImportMedicationPlanProposalToPatient
{
    public function import(Patient $patient, MedicationPlanProposal $proposal): void
    {
        foreach ($proposal->items as $item) {
            $schedule = $item->schedule;

            if ($schedule === null) {
                continue;
            }

            $intakeWeekdays = $schedule->weekdays
                ->pluck('weekday')
                ->map(static fn (mixed $weekday): int => is_int($weekday) ? $weekday : (int) (string) $weekday)
                ->values()
                ->all();

            $medication = $patient->medications()->create([
                'name' => $item->name,
                'dose' => $item->dose,
                'dose_unit' => $item->dose_unit,
                'type_medication' => $item->type_medication,
                'strength' => $item->strength,
                'note' => $item->note,
            ]);

            $createdSchedule = $medication->schedules()->create([
                'meal_timing' => $schedule->meal_timing,
                'intake_frequency' => $schedule->intake_frequency,
                'times_per_day' => $schedule->times_per_day,
                'dose_quantity' => $schedule->dose_quantity,
                'dose_time' => $schedule->dose_time,
                'snooze_time' => $schedule->snooze_time,
                'start_date' => $schedule->start_date,
                'end_date' => $schedule->end_date,
            ]);
            $createdSchedule->syncIntakeWeekdays($intakeWeekdays);

            $medication->stocks()->create([
                'current_stock' => $item->current_stock,
            ]);
        }
    }
}
