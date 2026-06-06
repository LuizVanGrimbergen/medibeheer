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
            'completed' => [
                'required_without_all:pickup_status,prescription_expiry_date',
                'boolean',
            ],
            'pickup_status' => [
                'required_without_all:completed,prescription_expiry_date',
                Rule::enum(MedicationPrescriptionPickupStatus::class),
            ],
            'prescription_expiry_date' => [
                'required_without_all:completed,pickup_status',
                'date',
                'date_format:Y-m-d',
            ],
        ];
    }
}
