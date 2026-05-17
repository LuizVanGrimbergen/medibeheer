<?php

namespace App\Http\Requests\Patient\Medications;

use App\Http\Requests\Patient\Medications\Concerns\AuthorizesRouteMedication;
use App\Http\Requests\Patient\Medications\Concerns\ValidatesMedicationScheduleFields;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicationScheduleRequest extends FormRequest
{
    use AuthorizesRouteMedication;
    use ValidatesMedicationScheduleFields;

    protected function prepareForValidation(): void
    {
        $this->mirrorMedicationDoseIntoDoseQuantity();

        $payload = MedicationScheduleIntakeWeekdays::normalizeFlatPayload([
            'intake_frequency' => $this->input('intake_frequency'),
            'intake_weekdays' => $this->input('intake_weekdays'),
        ]);

        $this->merge($this->normalizeMedicationScheduleDatesForValidation($payload));
    }

    public function authorize(): bool
    {
        return $this->userCanUpdateRouteMedication();
    }

    public function rules(): array
    {
        return $this->rulesMedicationScheduleFields();
    }
}
