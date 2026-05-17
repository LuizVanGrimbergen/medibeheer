<?php

namespace App\Models;

use App\Enums\MedicationDoseUnit;
use App\Enums\MedicationType;
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
}
