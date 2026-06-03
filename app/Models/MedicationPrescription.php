<?php

namespace App\Models;

use App\Enums\MedicationPrescriptionPickupStatus;
use App\Models\Concerns\LogsPatientDataChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicationPrescription extends Model
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
            'pickup_status',
            'completed_at',
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
}
