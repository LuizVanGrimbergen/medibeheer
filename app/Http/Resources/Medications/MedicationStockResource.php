<?php

namespace App\Http\Resources\Medications;

use App\Models\MedicationStock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MedicationStock */
class MedicationStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'medication_id' => $this->medication_id,
            'current_stock' => (string) $this->current_stock,
            'low_stock' => (string) $this->low_stock,
        ];
    }
}
