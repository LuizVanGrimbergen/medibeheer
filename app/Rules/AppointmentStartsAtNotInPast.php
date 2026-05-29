<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Validation\ValidationRule;

class AppointmentStartsAtNotInPast implements ValidationRule
{
    public function __construct(
        private readonly ?DateTimeInterface $permitIfUnchangedFrom = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! $value instanceof DateTimeInterface) {
            return;
        }

        try {
            $incoming = Carbon::parse($value);
        } catch (\Throwable) {
            return;
        }

        if ($this->permitIfUnchangedFrom !== null) {
            $permitted = Carbon::parse($this->permitIfUnchangedFrom);

            if ($incoming->equalTo($permitted)) {
                return;
            }
        }

        if ($incoming->lt(Carbon::now())) {
            $fail(trans('patient_appointments.starts_at_not_in_past'));
        }
    }
}
