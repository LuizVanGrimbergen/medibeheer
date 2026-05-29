<?php

namespace App\Models;

use App\Models\Concerns\LogsPatientDataChanges;
use App\Services\Audit\ActivityLogName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicationStock extends Model
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
        'current_stock',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'encrypted',
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

    protected static function booted(): void
    {
        static::updated(function (self $stock): void {
            if (! $stock->wasChanged('current_stock')) {
                return;
            }

            $causer = Auth::user();

            if ($causer === null) {
                return;
            }

            activity(ActivityLogName::DATA)
                ->causedBy($causer)
                ->performedOn($stock)
                ->withProperties([
                    'patient_id' => $stock->patient_id,
                    'medication_id' => $stock->medication_id,
                    'stock_changed' => true,
                ])
                ->log('medication_stock_updated');
        });
    }
}
