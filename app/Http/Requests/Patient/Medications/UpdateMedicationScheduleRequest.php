<?php

namespace App\Http\Requests\Patient\Medications;

use App\Http\Requests\Patient\Medications\Concerns\AuthorizesRouteMedication;
use App\Http\Requests\Patient\Medications\Concerns\ValidatesMedicationScheduleFields;
use App\Models\MedicationSchedule;
use App\Support\MedicationScheduleIntakeWeekdays;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicationScheduleRequest extends FormRequest
{
    use AuthorizesRouteMedication;
    use ValidatesMedicationScheduleFields;

    protected function prepareForValidation(): void
    {
        $this->mirrorMedicationDoseIntoDoseQuantity();

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

        if ($this->has('end_date')) {
            $this->merge($this->normalizeMedicationScheduleDatesForValidation([
                'end_date' => $this->input('end_date'),
            ]));
        }
    }

    public function authorize(): bool
    {
        $schedule = $this->route('schedule');
        $medication = $this->routeMedication();

        if ($medication === null || ! $schedule instanceof MedicationSchedule) {
            return false;
        }

        if (! $schedule->medication->is($medication)) {
            return false;
        }

        return $this->userCanUpdateRouteMedication();
    }

    public function rules(): array
    {
        return [
            ...$this->rulesMedicationScheduleFields(sometimes: true),
            'dose_quantity' => ['sometimes', 'string', 'max:500'],
        ];
    }
}
