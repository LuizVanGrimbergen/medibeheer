<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\DoctorType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    /**************************************/
    /*             Attributes */
    /**************************************/

    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_type',
        'provider_name',
        'address',
        'starts_at',
        'notes',
        'doctor_visit_summary',
        'cancellation_reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'doctor_type' => DoctorType::class,
            'provider_name' => 'encrypted',
            'address' => 'encrypted',
            'notes' => 'encrypted',
            'doctor_visit_summary' => 'encrypted',
            'cancellation_reason' => 'encrypted',
            'starts_at' => 'datetime',
            'status' => AppointmentStatus::class,
        ];
    }

    /**************************************/
    /*           Relationships */
    /**************************************/

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
    
    /**************************************/
    /*       Accessors / Mutators */
    /**************************************/

    /**************************************/
    /*              Scopes */
    /**************************************/

    /**************************************/
    /*              Helpers */
    /**************************************/
}
