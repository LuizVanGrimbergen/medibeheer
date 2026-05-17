<?php

namespace App\Http\Requests\Patient\Medications\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

trait ValidatesMedicationScheduleDateFields
{
    protected function normalizeMedicationScheduleDatesForValidation(array $schedule): array
    {
        $end = $schedule['end_date'] ?? null;

        if ($end === null || (is_string($end) && trim($end) === '')) {
            $schedule['end_date'] = null;
        }

        return $schedule;
    }

    protected function rulesMedicationScheduleStartAndEndDates(
        string $prefix = '',
        bool $sometimes = false,
        callable|bool|null $requiredWhen = null,
    ): array {
        $startKey = "{$prefix}start_date";
        $endKey = "{$prefix}end_date";

        $required = $requiredWhen === null ? 'required' : Rule::requiredIf($requiredWhen);
        $maybeSometimes = $sometimes ? ['sometimes'] : [];
        $endRequired = $requiredWhen === null ? [] : [Rule::requiredIf($requiredWhen)];

        return [
            $startKey => [...$maybeSometimes, $required, 'date_format:Y-m-d'],
            $endKey => [
                ...$endRequired,
                ...$maybeSometimes,
                'nullable',
                'date_format:Y-m-d',
                "after_or_equal:{$startKey}",
                $this->medicationScheduleCourseMaxDaysClosure($startKey),
            ],
        ];
    }

    private function medicationScheduleCourseMaxDaysClosure(string $startKey): \Closure
    {
        $resolveInput = fn (string $key): mixed => $this->validationInput($key);

        return function (mixed ...$parts) use ($startKey, $resolveInput): void {
            if (count($parts) !== 3 || ! ($fail = $parts[2]) instanceof \Closure) {
                return;
            }

            $value = $parts[1];
            $startRaw = $resolveInput($startKey);

            if (! is_string($value) || ! is_string($startRaw)) {
                return;
            }

            try {
                $startDay = Carbon::createFromFormat('Y-m-d', $startRaw)->startOfDay();
                $endDay = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            } catch (\Throwable) {
                return;
            }

            if ($startDay->diffInDays($endDay) + 1 > 366) {
                $fail(trans('medication.schedule_course_max_days'));
            }
        };
    }

    private function validationInput(string $key): mixed
    {
        return request()->input($key);
    }
}
