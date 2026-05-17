<?php

namespace App\Http\Requests\Patient\Medications\Concerns;

use App\Enums\MedicationIntakeFrequency;
use App\Enums\MedicationMealTiming;
use Illuminate\Validation\Rule;

trait ValidatesMedicationScheduleFields
{
    use ValidatesMedicationScheduleDateFields;

    protected function rulesMedicationScheduleFields(
        string $prefix = '',
        bool $sometimes = false,
        callable|bool|null $requiredWhen = null,
    ): array {
        $required = $requiredWhen === null ? 'required' : Rule::requiredIf($requiredWhen);
        $maybeSometimes = $sometimes ? ['sometimes'] : [];
        $frequencyKey = "{$prefix}intake_frequency";

        return [
            "{$prefix}meal_timing" => [
                ...$maybeSometimes,
                $required,
                Rule::enum(MedicationMealTiming::class),
            ],
            $frequencyKey => [
                ...$maybeSometimes,
                $required,
                Rule::in(MedicationIntakeFrequency::allowedValues()),
            ],
            "{$prefix}intake_weekdays" => [
                "exclude_unless:{$frequencyKey},weekdays",
                ...$maybeSometimes,
                'required',
                'array',
                'min:1',
            ],
            "{$prefix}intake_weekdays.*" => ['integer', Rule::in([1, 2, 3, 4, 5, 6, 7])],
            "{$prefix}times_per_day" => [
                ...$maybeSometimes,
                $required,
                'string',
                Rule::in(array_map(static fn (int $n): string => (string) $n, range(1, 24))),
            ],
            "{$prefix}dose_quantity" => [...$maybeSometimes, $required, 'string', 'max:500'],
            "{$prefix}dose_time" => [...$maybeSometimes, $required, 'string', 'max:500'],
            ...$this->rulesMedicationScheduleStartAndEndDates($prefix, $sometimes, $requiredWhen),
        ];
    }
}
