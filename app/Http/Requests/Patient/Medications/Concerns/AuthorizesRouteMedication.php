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
        return $this->userCanOnRouteMedication('update');
    }

    protected function userCanUpdateRouteMedicationStock(): bool
    {
        return $this->userCanOnRouteMedication('updateStock');
    }

    private function userCanOnRouteMedication(string $ability): bool
    {
        $medication = $this->routeMedication();

        if ($medication === null) {
            return false;
        }

        return $this->user()?->can($ability, $medication) ?? false;
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
