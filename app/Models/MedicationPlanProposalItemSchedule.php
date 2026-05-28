<?php

namespace App\Models;

use App\Enums\MedicationMealTiming;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationPlanProposalItemSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'medication_plan_proposal_item_id',
        'meal_timing',
        'intake_frequency',
        'times_per_day',
        'dose_quantity',
        'dose_time',
        'snooze_time',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'meal_timing' => MedicationMealTiming::class,
            'intake_frequency' => 'encrypted',
            'dose_quantity' => 'encrypted',
            'dose_time' => 'encrypted',
            'snooze_time' => 'encrypted',
            'times_per_day' => 'encrypted',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(MedicationPlanProposalItem::class, 'medication_plan_proposal_item_id');
    }

    public function weekdays(): HasMany
    {
        return $this->hasMany(MedicationPlanProposalItemScheduleWeekday::class);
    }

    public function syncIntakeWeekdays(?array $weekdayNumbers): void
    {
        $this->weekdays()->delete();

        if ($weekdayNumbers === null || $weekdayNumbers === []) {
            return;
        }

        $seen = [];

        foreach ($weekdayNumbers as $day) {
            $n = (int) $day;

            if ($n < 1 || $n > 7 || isset($seen[$n])) {
                continue;
            }

            $seen[$n] = true;
            $this->weekdays()->create(['weekday' => $n]);
        }
    }
}
