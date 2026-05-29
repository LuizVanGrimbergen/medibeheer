<?php

namespace App\Http\Resources\Medications;

use App\Models\MedicationSchedule;
use App\Support\Medications\MedicationScheduleDoseTimes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MedicationSchedule */
class MedicationScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'meal_timing' => $this->meal_timing->value,
            'intake_frequency' => $this->intake_frequency,
            'intake_weekdays' => $this->intake_weekdays,
            'times_per_day' => (string) $this->times_per_day,
            'dose_time' => (string) $this->dose_time,
            'snooze_time' => MedicationScheduleDoseTimes::displaySnoozeMinutes((string) ($this->snooze_time ?? '')),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
        ];
    }
}
