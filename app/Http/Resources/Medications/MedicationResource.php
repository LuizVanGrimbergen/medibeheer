<?php

declare(strict_types=1);

namespace App\Http\Resources\Medications;

use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Medication */
class MedicationResource extends JsonResource
{
    public static function collectForInertia(iterable $medications): array
    {
        return static::collection($medications)->resolve();
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'family_id' => $this->family_id,
            'name' => (string) $this->name,
            'dose' => filled($this->dose) ? (string) $this->dose : null,
            'dose_unit' => $this->dose_unit?->value,
            'type_medication' => $this->type_medication->value,
            'color' => $this->color?->value,
            'note' => filled($this->note) ? (string) $this->note : null,
            'schedules' => $this->relationLoaded('schedules')
                ? MedicationScheduleResource::collection($this->schedules)->resolve()
                : [],
            'stocks' => $this->relationLoaded('stocks')
                ? MedicationStockResource::collection($this->stocks)->resolve()
                : [],
        ];
    }
}
