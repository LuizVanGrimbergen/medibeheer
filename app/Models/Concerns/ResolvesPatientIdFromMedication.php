<?php

namespace App\Models\Concerns;

use App\Models\Medication;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

trait ResolvesPatientIdFromMedication
{
    public function patient(): HasOneThrough
    {
        return $this->hasOneThrough(
            Patient::class,
            Medication::class,
            'id',
            'id',
            'medication_id',
            'patient_id',
        );
    }
}
