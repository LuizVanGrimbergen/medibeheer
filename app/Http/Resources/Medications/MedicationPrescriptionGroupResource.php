<?php

declare(strict_types=1);

namespace App\Http\Resources\Medications;

use App\Models\Medication;
use App\Models\MedicationPrescription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Medication */
class MedicationPrescriptionGroupResource extends JsonResource
{
    public static function collectForInertia(iterable $medications): array
    {
        return static::collection($medications)->resolve();
    }

    public function toArray(Request $request): array
    {
        return [
            'medication' => MedicationPrescriptionMedicationResource::make($this->resource)->resolve(),
            'prescriptions' => $this->prescriptions
                ->map(fn (MedicationPrescription $prescription): array => [
                    'id' => $prescription->id,
                    'prescription_expiry_date' => $prescription->prescription_expiry_date?->format('Y-m-d'),
                    'pickup_status' => $prescription->pickup_status->value,
                ])
                ->values()
                ->all(),
        ];
    }
}
