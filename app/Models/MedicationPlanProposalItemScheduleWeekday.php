<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationPlanProposalItemScheduleWeekday extends Model
{
    use HasFactory;

    protected $fillable = [
        'medication_plan_proposal_item_schedule_id',
        'weekday',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => 'encrypted',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MedicationPlanProposalItemSchedule::class, 'medication_plan_proposal_item_schedule_id');
    }
}
