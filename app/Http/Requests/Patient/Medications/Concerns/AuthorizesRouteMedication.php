<?php

namespace App\Http\Requests\Patient\Medications\Concerns;

use App\Models\Medication;

trait AuthorizesRouteMedication
{
    protected function routeMedication(): ?Medication
    {
        $medication = $this->route('medication');

        return $medication instanceof Medication ? $medication : null;
    }

    protected function userCanUpdateRouteMedication(): bool
    {
        $medication = $this->routeMedication();

        if ($medication === null) {
            return false;
        }

        return $this->user()?->can('update', $medication) ?? false;
    }

    protected function mirrorMedicationDoseIntoDoseQuantity(): void
    {
        $medication = $this->routeMedication();

        if ($medication === null) {
            return;
        }

        $this->merge(['dose_quantity' => trim((string) $medication->dose)]);
    }
}
