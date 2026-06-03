<?php

declare(strict_types=1);

namespace App\Http\Resources\Medications;

use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Medication */
class MedicationPrescriptionMedicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => (string) $this->name,
            'type_medication' => $this->type_medication->value,
        ];
    }
}
