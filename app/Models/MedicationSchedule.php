<?php

namespace App\Models;

use App\Enums\MedicationMealTiming;
use App\Models\Concerns\LogsPatientDataChanges;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationSchedule extends Model
{
    use HasFactory;
    use LogsPatientDataChanges;
    use SoftDeletes;

    protected function patientDataActivityLogAttributes(): array
    {
        return [
            'patient_id',
            'family_id',
            'medication_id',
            'meal_timing',
            'start_date',
            'end_date',
            'deleted_at',
        ];
    }

    /**************************************/
    /*             Attributes */
    /**************************************/

    protected $fillable = [
        'patient_id',
        'family_id',
        'medication_id',
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

    protected function intakeWeekdays(): Attribute
    {
        return Attribute::make(
            get: function (): ?array {
                $this->loadMissing('weekdays');

                if ($this->weekdays->isEmpty()) {
                    return null;
                }

                return $this->weekdays
                    ->pluck('weekday')
                    ->map(static function (mixed $w): int {
                        return is_int($w) ? $w : (int) (string) $w;
                    })
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();
            },
        );
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

    public function intakes(): HasMany
    {
        return $this->hasMany(MedicationIntake::class);
    }

    public function weekdays(): HasMany
    {
        return $this->hasMany(MedicationScheduleWeekday::class);
    }
}
