<?php

namespace App\Http\Requests\Patient\Medications;

use App\Enums\MedicationColor;
use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationType;
use App\Http\Requests\Patient\Medications\Concerns\ValidatesMedicationScheduleDateFields;
use App\Models\Medication;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicationRequest extends FormRequest
{
    use ValidatesMedicationScheduleDateFields;

    protected function prepareForValidation(): void
    {
        $schedule = $this->input('schedule');

        if (is_array($schedule)) {
            $dose = $this->input('dose');
            $schedule['dose_quantity'] = is_string($dose) ? trim($dose) : '';
            $schedule = MedicationScheduleIntakeWeekdays::normalizeNestedSchedule($schedule);
            $this->merge(['schedule' => $schedule]);
        }

        if ($this->has('note')) {
            $trimmed = trim((string) $this->input('note'));

            $this->merge(['note' => $trimmed === '' ? null : $trimmed]);
        }

        if ($this->has('current_stock') || $this->has('low_stock')) {
            $currentStockInput = $this->input('current_stock');
            $lowStockInput = $this->input('low_stock');

            $currentTrimmed = is_string($currentStockInput) ? trim($currentStockInput) : '';
            $lowTrimmed = is_string($lowStockInput) ? trim($lowStockInput) : '';

            $this->merge([
                'current_stock' => $currentTrimmed === '' ? null : $currentTrimmed,
                'low_stock' => $lowTrimmed === '' ? null : $lowTrimmed,
            ]);
        }
    }

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
                'sometimes',
                'nullable',
                Rule::enum(MedicationDoseUnit::class),
            ],
            'type_medication' => ['sometimes', 'required', Rule::enum(MedicationType::class)],
            'color' => ['nullable', Rule::enum(MedicationColor::class)],
            'note' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'current_stock' => ['sometimes', 'required_with:low_stock', 'string', 'max:500'],
            'low_stock' => ['sometimes', 'required_with:current_stock', 'string', 'max:64'],
            'schedule' => ['sometimes', 'required', 'array'],
            'schedule.meal_timing' => [
                Rule::requiredIf($schedulePresent),
                Rule::enum(MedicationMealTiming::class),
            ],
            'schedule.intake_frequency' => [
                Rule::requiredIf($schedulePresent),
                Rule::in(MedicationIntakeFrequency::allowedValues()),
            ],
            'schedule.intake_weekdays' => [
                'exclude_unless:schedule.intake_frequency,weekdays',
                'required',
                'array',
                'min:1',
            ],
            'schedule.intake_weekdays.*' => ['integer', Rule::in([1, 2, 3, 4, 5, 6, 7])],
            'schedule.times_per_day' => [
                Rule::requiredIf($schedulePresent),
                'string',
                Rule::in(array_map(static fn (int $n): string => (string) $n, range(1, 24))),
            ],
            'schedule.dose_quantity' => [
                Rule::requiredIf($schedulePresent),
                'string',
                'max:500',
            ],
            'schedule.dose_time' => [
                Rule::requiredIf($schedulePresent),
                'string',
                'max:500',
            ],
            ...$this->rulesMedicationScheduleStartAndEndDatesNestedUnderScheduleWhen($schedulePresent),
        ];
    }
}
