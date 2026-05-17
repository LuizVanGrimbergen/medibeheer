<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationScheduleWeekday extends Model
{
    protected $fillable = [
        'medication_schedule_id',
        'weekday',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => 'encrypted',
        ];
    }

    public function medicationSchedule(): BelongsTo
    {
        return $this->belongsTo(MedicationSchedule::class);
    }
}
