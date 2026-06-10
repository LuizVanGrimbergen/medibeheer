<?php

namespace App\Models;

use App\Models\Concerns\LogsPatientDataChanges;
use App\Models\Concerns\ResolvesPatientIdFromMedication;
use App\Services\Audit\ActivityLogName;
use App\Support\Medications\PushReminders\LowStock\ReminderCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicationStock extends Model
{
    use HasFactory;
    use LogsPatientDataChanges;
    use ResolvesPatientIdFromMedication;
    use SoftDeletes;

    protected function patientDataActivityLogAttributes(): array
    {
        return [
            'medication_id',
            'deleted_at',
        ];
    }

    /**************************************/
    /*             Attributes */
    /**************************************/

    protected $fillable = [
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

            app(ReminderCache::class)->clearIfSupplyRecovered($stock);

            $causer = Auth::user();

            if ($causer === null) {
                return;
            }

            activity(ActivityLogName::DATA)
                ->causedBy($causer)
                ->performedOn($stock)
                ->withProperties([
                    'patient_id' => $stock->patientDataActivityPatientId(),
                    'medication_id' => $stock->medication_id,
                    'stock_changed' => true,
                ])
                ->log('medication_stock_updated');
        });
    }
}
