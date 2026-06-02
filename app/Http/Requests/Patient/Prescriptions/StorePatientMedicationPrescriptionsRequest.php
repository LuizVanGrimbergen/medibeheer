<?php

namespace App\Http\Requests\Patient\Prescriptions;

use App\Http\Requests\Patient\Medications\Concerns\AuthorizesRouteMedication;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientMedicationPrescriptionsRequest extends FormRequest
{
    use AuthorizesRouteMedication;

    public function authorize(): bool
    {
        $medication = $this->routeMedication();

        if ($medication === null) {
            return false;
        }

        return $this->user()?->can('storePrescription', $medication) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $quantity = $this->input('quantity');

        if (is_numeric($quantity)) {
            $this->merge([
                'quantity' => (int) $quantity,
            ]);
        }
    }

    public function rules(): array
    {
        $quantity = (int) $this->input('quantity', 0);

        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:24'],
            'prescription_expiry_dates' => ['required', 'array', "size:{$quantity}"],
            'prescription_expiry_dates.*' => ['required', 'date', 'date_format:Y-m-d'],
        ];
    }
}
