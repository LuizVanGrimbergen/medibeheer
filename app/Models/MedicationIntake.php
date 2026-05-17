<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationIntake extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'medication_id',
        'medication_schedule_id',
        'intake_date',
        'dose_time',
        'taken_at',
    ];

    protected function casts(): array
    {
        return [
            'intake_date' => 'date',
            'taken_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MedicationSchedule::class, 'medication_schedule_id');
    }
}
