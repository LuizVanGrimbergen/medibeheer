<?php

namespace App\Http\Requests\Patient\Medications\Concerns;

trait NormalizesNullableStringInputs
{
    protected function mergeTrimmedOrNull(string $key): void
    {
        $raw = $this->input($key);
        $trimmed = is_string($raw) ? trim($raw) : '';

        $this->merge([$key => $trimmed === '' ? null : $trimmed]);
    }

    protected function mergeTrimmedOrNullIfPresent(string $key): void
    {
        if (! $this->has($key)) {
            return;
        }

        $this->mergeTrimmedOrNull($key);
    }
}
