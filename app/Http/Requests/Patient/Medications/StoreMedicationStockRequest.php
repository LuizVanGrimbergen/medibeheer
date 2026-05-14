<?php

namespace App\Http\Requests\Patient\Medications;

use App\Models\Medication;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicationStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        $medication = $this->route('medication');

        if (! $medication instanceof Medication) {
            return false;
        }

        return $this->user()?->can('update', $medication) ?? false;
    }

    public function rules(): array
    {
        return [
            'current_stock' => ['required', 'string', 'max:500'],
            'low_stock' => ['required', 'string', 'max:64'],
        ];
    }
}
