<?php

declare(strict_types=1);

namespace App\Http\Resources\Medications;

use App\Models\Medication;
use App\Services\Medications\MedicationSupplyEstimateService;
use App\Support\Medications\MedicationListClassifier;
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
        $supplyEstimateDays = null;
        $supplyEstimateQuality = 'unknown';

        if ($this->relationLoaded('stocks') && $this->relationLoaded('schedules')) {
            $estimate = app(MedicationSupplyEstimateService::class)->estimate($this->resource);
            $supplyEstimateDays = $estimate['days'];
            $supplyEstimateQuality = $estimate['quality'];
        }

        $listStatus = app(MedicationListClassifier::class)->statusFor($this->resource);

        return [
            'id' => $this->id,
            'list_status' => $listStatus->value,
            'name' => (string) $this->name,
            'dose' => filled($this->dose) ? (string) $this->dose : null,
            'dose_unit' => $this->dose_unit?->value,
            'type_medication' => $this->type_medication->value,
            'strength' => filled($this->strength) ? (string) $this->strength : null,
            'note' => filled($this->note) ? (string) $this->note : null,
            'prescription_expiry_date' => $this->relationLoaded('prescription')
                ? $this->prescription?->prescription_expiry_date?->format('Y-m-d')
                : null,
            'stock_pieces_per_package' => filled($this->stock_pieces_per_package)
                ? (int) $this->stock_pieces_per_package
                : null,
            'schedules' => $this->relationLoaded('schedules')
                ? MedicationScheduleResource::collection($this->schedules)->resolve()
                : [],
            'stocks' => $this->relationLoaded('stocks')
                ? MedicationStockResource::collection($this->stocks)->resolve()
                : [],
            'supply_estimate_days' => $supplyEstimateDays,
            'supply_estimate_quality' => $supplyEstimateQuality,
        ];
    }
}
