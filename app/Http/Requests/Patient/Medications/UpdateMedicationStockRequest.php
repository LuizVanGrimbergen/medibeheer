<?php

namespace App\Http\Requests\Patient\Medications;

use App\Models\Medication;
use App\Models\MedicationStock;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicationStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        $medication = $this->route('medication');
        $stock = $this->route('stock');

        if (! $medication instanceof Medication || ! $stock instanceof MedicationStock) {
            return false;
        }

        if (! $stock->medication->is($medication)) {
            return false;
        }

        return $this->user()?->can('update', $medication) ?? false;
    }

    public function rules(): array
    {
        return [
            'current_stock' => ['sometimes', 'required', 'string', 'max:500'],
            'low_stock' => ['sometimes', 'required', 'string', 'max:64'],
        ];
    }
}
