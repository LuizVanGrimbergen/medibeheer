<?php

namespace App\Http\Requests\Patient\Medications;

use App\Enums\MedicationType;
use App\Http\Requests\Patient\Medications\Concerns\NormalizesNullableStringInputs;
use App\Http\Requests\Patient\Medications\Concerns\ValidatesMedicationScheduleFields;
use App\Http\Requests\Patient\Medications\Concerns\ValidatesMedicationStrengthField;
use App\Models\Medication;
use App\Support\MedicationScheduleDoseTimeFields;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationRequest extends FormRequest
{
    use NormalizesNullableStringInputs;
    use ValidatesMedicationScheduleFields;
    use ValidatesMedicationStrengthField;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Medication::class) ?? false;
    }

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

        $this->mergeTrimmedOrNull('note');
        $this->mergeTrimmedOrNull('strength');
        $this->mergeTrimmedOrNull('current_stock');
        $this->mergeTrimmedOrNull('prescription_expiry_date');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:500'],
            'dose' => ['required', 'string', 'max:500'],
            'dose_unit' => $this->rulesMedicationDoseUnitFieldForCreate(),
            'type_medication' => ['required', Rule::enum(MedicationType::class)],
            'strength' => $this->rulesMedicationStrengthField(),
            'note' => ['nullable', 'string', 'max:2000'],
            'prescription_expiry_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'current_stock' => ['required', 'string', 'max:500'],
            'stock_pieces_per_package' => ['required', 'integer', 'min:1', 'max:9999'],
            'schedule' => ['required', 'array'],
            ...$this->rulesMedicationScheduleFields(prefix: 'schedule.'),
        ];
    }
}
