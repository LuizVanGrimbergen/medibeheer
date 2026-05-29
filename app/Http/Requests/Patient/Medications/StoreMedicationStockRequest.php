<?php

namespace App\Http\Requests\Patient\Medications;

use App\Http\Requests\Patient\Medications\Concerns\AuthorizesRouteMedication;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicationStockRequest extends FormRequest
{
    use AuthorizesRouteMedication;

    public function authorize(): bool
    {
        return $this->userCanUpdateRouteMedication();
    }

    public function rules(): array
    {
        return [
            'current_stock' => ['required', 'string', 'max:500'],
        ];
    }
}
