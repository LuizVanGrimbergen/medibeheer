<?php

declare(strict_types=1);

namespace App\Http\Requests\Patient\Prescriptions;

use App\Enums\MedicationPrescriptionPickupStatus;
use App\Models\MedicationPrescription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientMedicationPrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $prescription = $this->route('medication_prescription');

        if (! $prescription instanceof MedicationPrescription) {
            return false;
        }

        return $this->user()?->can('update', $prescription) ?? false;
    }

    public function rules(): array
    {
        return [
            'completed' => ['required_without:pickup_status', 'boolean'],
            'pickup_status' => [
                'required_without:completed',
                Rule::enum(MedicationPrescriptionPickupStatus::class),
            ],
        ];
    }
}
