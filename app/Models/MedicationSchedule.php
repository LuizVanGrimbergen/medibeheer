<?php

namespace App\Models;

use App\Enums\MedicationMealTiming;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationSchedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**************************************/
    /*             Attributes */
    /**************************************/

    protected $fillable = [
        'patient_id',
        'family_id',
        'medication_id',
        'meal_timing',
        'intake_frequency',
        'intake_weekdays',
        'times_per_day',
        'dose_quantity',
        'dose_time',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'meal_timing' => MedicationMealTiming::class,
            'intake_frequency' => 'encrypted',
            'intake_weekdays' => 'array',
            'dose_quantity' => 'encrypted',
            'dose_time' => 'encrypted',
            'times_per_day' => 'encrypted',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**************************************/
    /*           Relationships */
    /**************************************/

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
