<?php

namespace App\Http\Requests\Patient\Medications;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Enums\MedicationType;
use App\Http\Requests\Patient\Medications\Concerns\ValidatesMedicationScheduleDateFields;
use App\Models\Medication;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicationRequest extends FormRequest
{
    use ValidatesMedicationScheduleDateFields;

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

            $this->merge(['schedule' => $schedule]);
        }

        $note = $this->input('note');

        if ($note === null || (is_string($note) && trim($note) === '')) {
            $this->merge(['note' => null]);
        } else {
            $this->merge(['note' => trim((string) $note)]);
        }

        $currentStockInput = $this->input('current_stock');
        $lowStockInput = $this->input('low_stock');

        $currentTrimmed = is_string($currentStockInput) ? trim($currentStockInput) : '';
        $lowTrimmed = is_string($lowStockInput) ? trim($lowStockInput) : '';

        $this->merge([
            'current_stock' => $currentTrimmed === '' ? null : $currentTrimmed,
            'low_stock' => $lowTrimmed === '' ? null : $lowTrimmed,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:500'],
            'dose' => ['required', 'string', 'max:500'],
            'dose_unit' => ['required', Rule::enum(MedicationDoseUnit::class)],
            'type_medication' => ['required', Rule::enum(MedicationType::class)],
            'note' => ['nullable', 'string', 'max:2000'],
            'current_stock' => ['required', 'string', 'max:500'],
            'low_stock' => ['required', 'string', 'max:64'],
            'schedule' => ['required', 'array'],
            'schedule.meal_timing' => ['required', Rule::enum(MedicationMealTiming::class)],
            'schedule.intake_frequency' => ['required', Rule::in(MedicationIntakeFrequency::allowedValues())],
            'schedule.intake_weekdays' => [
                'exclude_unless:schedule.intake_frequency,weekdays',
                'required',
                'array',
                'min:1',
            ],
            'schedule.intake_weekdays.*' => ['integer', Rule::in([1, 2, 3, 4, 5, 6, 7])],
            'schedule.times_per_day' => [
                'required',
                'string',
                Rule::in(array_map(static fn (int $n): string => (string) $n, range(1, 24))),
            ],
            'schedule.dose_quantity' => ['required', 'string', 'max:500'],
            'schedule.dose_time' => ['required', 'string', 'max:500'],
            ...$this->rulesMedicationScheduleStartAndEndDatesNestedUnderSchedule(),
        ];
    }
}
