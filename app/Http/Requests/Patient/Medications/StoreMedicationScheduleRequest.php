<?php

namespace App\Http\Requests\Patient\Medications;

use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Http\Requests\Patient\Medications\Concerns\ValidatesMedicationScheduleDateFields;
use App\Models\Medication;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationScheduleRequest extends FormRequest
{
    use ValidatesMedicationScheduleDateFields;

    protected function prepareForValidation(): void
    {
        $medication = $this->route('medication');

        if ($medication instanceof Medication) {
            $this->merge(['dose_quantity' => trim((string) $medication->dose)]);
        }

        $this->merge(MedicationScheduleIntakeWeekdays::normalizeFlatPayload([
            'intake_frequency' => $this->input('intake_frequency'),
            'intake_weekdays' => $this->input('intake_weekdays'),
        ]));
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
        return [
            'meal_timing' => ['required', Rule::enum(MedicationMealTiming::class)],
            'intake_frequency' => ['required', Rule::in(MedicationIntakeFrequency::allowedValues())],
            'intake_weekdays' => [
                'exclude_unless:intake_frequency,weekdays',
                'required',
                'array',
                'min:1',
            ],
            'intake_weekdays.*' => ['integer', Rule::in([1, 2, 3, 4, 5, 6, 7])],
            'times_per_day' => [
                'required',
                'string',
                Rule::in(array_map(static fn (int $n): string => (string) $n, range(1, 24))),
            ],
            'dose_quantity' => ['required', 'string', 'max:500'],
            'dose_time' => ['required', 'string', 'max:500'],
            ...$this->rulesMedicationScheduleStartAndEndDatesTopLevel(),
        ];
    }
}
