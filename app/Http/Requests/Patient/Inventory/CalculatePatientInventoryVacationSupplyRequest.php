<?php

declare(strict_types=1);

namespace App\Http\Requests\Patient\Inventory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

final class CalculatePatientInventoryVacationSupplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->patient !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'starts_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $startsOn = $this->input('starts_on');
            $endsOn = $this->input('ends_on');

            if (! is_string($startsOn) || ! is_string($endsOn)) {
                return;
            }

            try {
                $startDay = Carbon::createFromFormat('Y-m-d', $startsOn)->startOfDay();
                $endDay = Carbon::createFromFormat('Y-m-d', $endsOn)->startOfDay();
            } catch (\Throwable) {
                return;
            }

            if ($startDay->diffInDays($endDay) + 1 > 366) {
                $validator->errors()->add(
                    'ends_on',
                    trans('medication.vacation_period_max_days'),
                );
            }
        });
    }
}
