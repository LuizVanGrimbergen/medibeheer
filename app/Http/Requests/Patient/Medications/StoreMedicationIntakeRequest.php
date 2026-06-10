<?php

namespace App\Http\Requests\Patient\Medications;

use App\Models\MedicationIntake;
use App\Models\MedicationSchedule;
use App\Support\Medications\MedicationIntakeClock;
use App\Support\Medications\MedicationScheduleDoseTimes;
use App\Support\Medications\MedicationScheduleOccursOnDate;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMedicationIntakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MedicationIntake::class) ?? false;
    }

    public function rules(): array
    {
        $patient = $this->user()?->patient;

        return [
            'medication_schedule_id' => [
                'required',
                'integer',
                Rule::exists((new MedicationSchedule)->getTable(), 'id')
                    ->where(function ($query) use ($patient): void {
                        if ($patient === null) {
                            $query->whereRaw('0 = 1');

                            return;
                        }

                        $query->whereIn(
                            'medication_id',
                            $patient->medications()->select('medications.id'),
                        );
                    }),
            ],
            'dose_time' => ['required', 'string', 'max:5', 'regex:/^\d{1,2}:\d{2}$/'],
            'late_intake' => ['sometimes', 'boolean'],
            'taken_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $patient = $this->user()?->patient;

            if ($patient === null) {
                $validator->errors()->add('medication_schedule_id', trans('validation.exists', [
                    'attribute' => 'medication_schedule_id',
                ]));

                return;
            }

            $schedule = MedicationSchedule::query()
                ->with('medication')
                ->find($this->integer('medication_schedule_id'));

            if ($schedule === null || $schedule->medication?->patient_id !== $patient->id) {
                return;
            }

            $doseTime = (string) $this->input('dose_time');

            $occursOnDate = app(MedicationScheduleOccursOnDate::class);

            if (! $occursOnDate->hasScheduledDoseOn($schedule, $doseTime, MedicationIntakeClock::today())) {
                $validator->errors()->add(
                    'medication_schedule_id',
                    trans('medication.intake_slot_not_due_today'),
                );

                return;
            }

            if ($this->boolean('late_intake') || $this->filled('taken_at')) {
                if ($this->filled('taken_at')) {
                    $takenAt = CarbonImmutable::parse(
                        (string) $this->input('taken_at'),
                        MedicationIntakeClock::TIMEZONE,
                    );

                    if (! $takenAt->isSameDay(MedicationIntakeClock::now())) {
                        $validator->errors()->add(
                            'taken_at',
                            trans('medication.intake_taken_at_not_today'),
                        );
                    }

                    if ($takenAt->isFuture()) {
                        $validator->errors()->add(
                            'taken_at',
                            trans('medication.intake_taken_at_in_future'),
                        );
                    }
                }

                return;
            }

            if (! MedicationScheduleDoseTimes::isWithinIntakeWindow(
                $doseTime,
                (string) $schedule->dose_time,
                $schedule->snooze_time,
            )) {
                $validator->errors()->add(
                    'dose_time',
                    trans('medication.intake_outside_window'),
                );
            }
        });
    }
}
