<?php

namespace App\Http\Requests\Patient\Medications\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

trait ValidatesMedicationScheduleDateFields
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rulesMedicationScheduleStartAndEndDatesNestedUnderScheduleWhen(
        callable $requiredWhen,
    ): array {
        $prefix = 'schedule.';
        $startKey = "{$prefix}start_date";
        $endKey = "{$prefix}end_date";

        return [
            $startKey => [
                Rule::requiredIf($requiredWhen),
                'date_format:Y-m-d',
            ],
            $endKey => [
                Rule::requiredIf($requiredWhen),
                'date_format:Y-m-d',
                "after_or_equal:{$startKey}",
                $this->medicationScheduleCourseMaxDaysClosure($startKey),
            ],
        ];
    }

    protected function rulesMedicationScheduleStartAndEndDatesNestedUnderSchedule(): array
    {
        return $this->medicationScheduleStartAndEndDateRules('schedule.');
    }

    protected function rulesMedicationScheduleStartAndEndDatesTopLevel(): array
    {
        return $this->medicationScheduleStartAndEndDateRules('');
    }

    protected function rulesMedicationScheduleStartAndEndDatesTopLevelSometimes(): array
    {
        return $this->medicationScheduleStartAndEndDateRules('', sometimes: true);
    }

    private function medicationScheduleStartAndEndDateRules(string $prefix, bool $sometimes = false): array
    {
        $startKey = "{$prefix}start_date";
        $endKey = "{$prefix}end_date";

        $startRules = $sometimes
            ? ['sometimes', 'required', 'date_format:Y-m-d']
            : ['required', 'date_format:Y-m-d'];

        $endRules = [
            ...($sometimes ? ['sometimes'] : []),
            'required',
            'date_format:Y-m-d',
            "after_or_equal:{$startKey}",
            $this->medicationScheduleCourseMaxDaysClosure($startKey),
        ];

        return [
            $startKey => $startRules,
            $endKey => $endRules,
        ];
    }

    private function medicationScheduleCourseMaxDaysClosure(string $startKey): \Closure
    {
        return function (mixed ...$parts) use ($startKey): void {
            if (count($parts) !== 3 || ! ($fail = $parts[2]) instanceof \Closure) {
                return;
            }

            $value = $parts[1];
            $startRaw = $this->input($startKey);

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
}
