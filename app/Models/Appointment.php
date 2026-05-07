<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentTransportStatus;
use App\Enums\DoctorType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'needs_transport',
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
            'needs_transport' => 'boolean',
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

    public function transportFamily(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
    }

    public function transportInvitations(): HasMany
    {
        return $this->hasMany(AppointmentTransportInvitation::class);
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

    public function transportStatus(bool $hasPendingTransportInvitation): ?AppointmentTransportStatus
    {
        if (! $this->needs_transport) {
            return null;
        }

        return match (true) {
            $this->transportFamily?->user !== null => AppointmentTransportStatus::ACCEPTED,
            $hasPendingTransportInvitation => AppointmentTransportStatus::REQUESTED,
            default => AppointmentTransportStatus::DECLINED,
        };
    }

    public function transportFamilyPayload(): ?array
    {
        $family = $this->transportFamily;
        $user = $family?->user;

        if ($family === null || $user === null) {
            return null;
        }

        return [
            'id' => (int) $family->id,
            'name' => (string) $user->name,
        ];
    }
}
