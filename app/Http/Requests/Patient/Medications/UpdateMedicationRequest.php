<?php

namespace App\Http\Requests\Patient\Medications;

use App\Enums\MedicationType;
use App\Http\Requests\Patient\Medications\Concerns\AuthorizesRouteMedication;
use App\Http\Requests\Patient\Medications\Concerns\NormalizesNullableStringInputs;
use App\Http\Requests\Patient\Medications\Concerns\ValidatesMedicationScheduleFields;
use App\Http\Requests\Patient\Medications\Concerns\ValidatesMedicationStrengthField;
use App\Support\MedicationScheduleDoseTimeFields;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicationRequest extends FormRequest
{
    use AuthorizesRouteMedication;
    use NormalizesNullableStringInputs;
    use ValidatesMedicationScheduleFields;
    use ValidatesMedicationStrengthField;

    protected function prepareForValidation(): void
    {
        $schedule = $this->input('schedule');

        if (is_array($schedule)) {
            $dose = $this->input('dose');
            $schedule['dose_quantity'] = is_string($dose) ? trim($dose) : '';
            $schedule = MedicationScheduleIntakeWeekdays::normalizeNestedSchedule($schedule);
            $schedule = MedicationScheduleDoseTimeFields::normalizeNestedSchedule($schedule);
            $schedule = $this->normalizeMedicationScheduleDatesForValidation($schedule);
            $this->merge(['schedule' => $schedule]);
        }

        $this->mergeTrimmedOrNullIfPresent('note');
        $this->mergeTrimmedOrNullIfPresent('strength');
        $this->mergeTrimmedOrNullIfPresent('current_stock');
        $this->mergeTrimmedOrNullIfPresent('prescription_expiry_date');
    }

    public function authorize(): bool
    {
        return $this->userCanUpdateRouteMedication();
    }

    public function rules(): array
    {
        $schedulePresent = fn (): bool => $this->filled('schedule');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:500'],
            'dose' => [
                Rule::requiredIf($schedulePresent),
                'nullable',
                'string',
                'max:500',
            ],
            'dose_unit' => [
                Rule::requiredIf($schedulePresent),
                ...$this->rulesMedicationDoseUnitField(sometimes: true),
            ],
            'type_medication' => ['sometimes', 'required', Rule::enum(MedicationType::class)],
            'strength' => $this->rulesMedicationStrengthField(sometimes: true),
            'note' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'prescription_expiry_date' => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d'],
            'current_stock' => ['sometimes', 'required', 'string', 'max:500'],
            'stock_pieces_per_package' => ['sometimes', 'required', 'integer', 'min:1', 'max:9999'],
            'schedule' => ['sometimes', 'required', 'array'],
            ...$this->rulesMedicationScheduleFields(
                prefix: 'schedule.',
                requiredWhen: $schedulePresent,
            ),
        ];
    }
}
