<?php

declare(strict_types=1);

namespace App\Http\Resources\Medications;

use App\Models\MedicationPrescription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MedicationPrescription */
class MedicationPrescriptionResource extends JsonResource
{
    public static function collectForInertia(iterable $prescriptions): array
    {
        return static::collection($prescriptions)->resolve();
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medication_id' => $this->medication_id,
            'prescription_expiry_date' => $this->prescription_expiry_date?->format('Y-m-d'),
            'is_last_in_batch' => $this->is_last_in_batch,
            'pickup_status' => $this->pickup_status->value,
            'medication' => $this->relationLoaded('medication')
                ? MedicationPrescriptionMedicationResource::make($this->medication)->resolve()
                : null,
        ];
    }
}
