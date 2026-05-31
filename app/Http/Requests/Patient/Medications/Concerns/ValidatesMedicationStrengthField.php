<?php

namespace App\Http\Requests\Patient\Medications\Concerns;

use App\Enums\MedicationDoseUnit;
use App\Models\Medication;
use Illuminate\Validation\Rule;

trait ValidatesMedicationStrengthField
{
    protected function rulesMedicationStrengthField(bool $sometimes = false): array
    {
        return [
            ...($sometimes ? ['sometimes'] : []),
            Rule::requiredIf(fn (): bool => $this->medicationStrengthIsRequired()),
            'nullable',
            'string',
            'max:500',
        ];
    }

    protected function rulesMedicationDoseUnitField(bool $sometimes = false): array
    {
        return [
            ...($sometimes ? ['sometimes'] : []),
            'required',
            Rule::enum(MedicationDoseUnit::class)->except(MedicationDoseUnit::UNIT),
        ];
    }

    protected function medicationStrengthIsRequired(): bool
    {
        if ($this->has('dose_unit')) {
            $unit = MedicationDoseUnit::tryFrom((string) $this->input('dose_unit'));

            return $unit?->requiresStrength() ?? false;
        }

        if (! $this->has('strength')) {
            return false;
        }

        $medication = $this->route('medication');

        if (! $medication instanceof Medication) {
            return false;
        }

        return $medication->dose_unit?->requiresStrength() ?? false;
    }

    public function messages(): array
    {
        return [
            'strength.required' => trans('medication.strength_required'),
        ];
    }
}
