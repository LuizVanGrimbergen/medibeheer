<?php

namespace App\Http\Requests\Patient\Medications;

use App\Http\Requests\Patient\Medications\Concerns\AuthorizesRouteMedication;
use App\Models\MedicationStock;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicationStockRequest extends FormRequest
{
    use AuthorizesRouteMedication;

    public function authorize(): bool
    {
        $stock = $this->route('stock');
        $medication = $this->routeMedication();

        if ($medication === null || ! $stock instanceof MedicationStock) {
            return false;
        }

        if (! $stock->medication->is($medication)) {
            return false;
        }

        return $this->userCanUpdateRouteMedication();
    }

    public function rules(): array
    {
        return [
            'current_stock' => ['sometimes', 'required', 'string', 'max:500'],
        ];
    }
}
