<?php

namespace App\Models;

use App\Enums\MedicationPrescriptionPickupStatus;
use App\Models\Concerns\LogsPatientDataChanges;
use App\Models\Concerns\ResolvesPatientIdFromMedication;
use App\Support\Medications\PushReminders\PrescriptionExpiry\ReminderCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationPrescription extends Model
{
    use HasFactory;
    use LogsPatientDataChanges;
    use ResolvesPatientIdFromMedication;
    use SoftDeletes;

    protected function patientDataActivityLogAttributes(): array
    {
        return [
            'medication_id',
            'pickup_status',
            'completed_at',
            'deleted_at',
        ];
    }

    /**************************************/
    /*             Attributes */
    /**************************************/

    protected $fillable = [
        'medication_id',
        'prescription_expiry_date',
        'is_last_in_batch',
        'pickup_status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'prescription_expiry_date' => 'date',
            'is_last_in_batch' => 'boolean',
            'pickup_status' => MedicationPrescriptionPickupStatus::class,
            'completed_at' => 'datetime',
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
        static::creating(function (self $prescription): void {
            if ($prescription->pickup_status === null) {
                $prescription->pickup_status = MedicationPrescriptionPickupStatus::PENDING;
            }
        });

        static::updated(function (self $prescription): void {
            if (! $prescription->wasChanged(['prescription_expiry_date', 'completed_at'])) {
                return;
            }

            app(ReminderCache::class)->clearIfNoLongerCritical($prescription);
        });

        static::deleted(function (self $prescription): void {
            app(ReminderCache::class)->forgetAllTiersForPrescriptionRecipients($prescription);
        });
    }
}
