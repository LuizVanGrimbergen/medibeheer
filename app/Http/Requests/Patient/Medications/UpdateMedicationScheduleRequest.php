<?php

namespace App\Http\Requests\Patient\Medications;

use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use App\Http\Requests\Patient\Medications\Concerns\ValidatesMedicationScheduleDateFields;
use App\Models\Medication;
use App\Models\MedicationSchedule;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicationScheduleRequest extends FormRequest
{
    use ValidatesMedicationScheduleDateFields;

    protected function prepareForValidation(): void
    {
        $medication = $this->route('medication');

        if ($medication instanceof Medication) {
            $this->merge(['dose_quantity' => trim((string) $medication->dose)]);
        }

        if (! $this->has('intake_frequency') && ! $this->has('intake_weekdays')) {
            return;
        }

        $scheduleModel = $this->route('schedule');

        if (! $scheduleModel instanceof MedicationSchedule) {
            return;
        }

        $frequency = $this->input('intake_frequency', $scheduleModel->intake_frequency);
        $weekdays = $this->input('intake_weekdays', $scheduleModel->intake_weekdays);

        $this->merge(MedicationScheduleIntakeWeekdays::normalizeFlatPayload([
            'intake_frequency' => $frequency,
            'intake_weekdays' => $weekdays,
        ]));
    }

    public function authorize(): bool
    {
        $medication = $this->route('medication');
        $schedule = $this->route('schedule');

        if (! $medication instanceof Medication || ! $schedule instanceof MedicationSchedule) {
            return false;
        }

        if (! $schedule->medication->is($medication)) {
            return false;
        }

        return $this->user()?->can('update', $medication) ?? false;
    }

    public function rules(): array
    {
        return [
            'meal_timing' => ['sometimes', 'required', Rule::enum(MedicationMealTiming::class)],
            'intake_frequency' => ['sometimes', 'required', Rule::in(MedicationIntakeFrequency::allowedValues())],
            'intake_weekdays' => [
                'exclude_unless:intake_frequency,weekdays',
                'sometimes',
                'required',
                'array',
                'min:1',
            ],
            'intake_weekdays.*' => ['integer', Rule::in([1, 2, 3, 4, 5, 6, 7])],
            'times_per_day' => [
                'sometimes',
                'required',
                'string',
                Rule::in(array_map(static fn (int $n): string => (string) $n, range(1, 24))),
            ],
            'dose_quantity' => ['sometimes', 'string', 'max:500'],
            'dose_time' => ['sometimes', 'required', 'string', 'max:500'],
            ...$this->rulesMedicationScheduleStartAndEndDatesTopLevelSometimes(),
        ];
    }
}
