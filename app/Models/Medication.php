<?php

namespace App\Models;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationType;
use App\Models\Concerns\LogsPatientDataChanges;
use App\Support\Medications\MedicationIntakeClock;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use HasFactory;
    use LogsPatientDataChanges;
    use SoftDeletes;

    protected function patientDataActivityLogAttributes(): array
    {
        return [
            'patient_id',
            'family_id',
            'dose_unit',
            'type_medication',
            'deleted_at',
        ];
    }

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
        'stock_pieces_per_package',
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
            'stock_pieces_per_package' => 'encrypted',
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

    public function prescriptions(): HasMany
    {
        return $this->hasMany(MedicationPrescription::class);
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(MedicationPrescription::class)->latestOfMany();
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
