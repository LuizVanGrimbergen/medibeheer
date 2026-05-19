<?php

namespace App\Models;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationType;
use App\Support\Medications\MedicationIntakeClock;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**************************************/
    /*             Attributes */
    /**************************************/

    protected $fillable = [
        'patient_id',
        'family_id',
        'name',
        'dose',
        'dose_unit',
        'type_medication',
        'strength',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'encrypted',
            'dose' => 'encrypted',
            'dose_unit' => MedicationDoseUnit::class,
            'type_medication' => MedicationType::class,
            'strength' => 'encrypted',
            'note' => 'encrypted',
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

    public function schedules(): HasMany
    {
        return $this->hasMany(MedicationSchedule::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(MedicationStock::class);
    }

    public function intakes(): HasMany
    {
        return $this->hasMany(MedicationIntake::class);
    }

    /**************************************/
    /*              Scopes */
    /**************************************/

    #[Scope]
    protected function activeOnMedicationList(Builder $query): Builder
    {
        $today = MedicationIntakeClock::today()->toDateString();

        return $query->where(function (Builder $query) use ($today): void {
            $query->whereDoesntHave('schedules')
                ->orWhereHas('schedules', function (Builder $schedule) use ($today): void {
                    $schedule->where(function (Builder $schedule) use ($today): void {
                        $schedule->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $today);
                    });
                });
        });
    }
}
