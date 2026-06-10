<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentTransportStatus;
use App\Enums\DoctorType;
use App\Models\Concerns\LogsPatientDataChanges;
use App\Models\Concerns\MaintainsBlindIndexForEncryptedEnum;
use App\Support\BlindIndex;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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
    use LogsPatientDataChanges;
    use MaintainsBlindIndexForEncryptedEnum;

    protected function patientDataActivityLogAttributes(): array
    {
        return [
            'patient_id',
            'family_id',
            'doctor_type',
            'starts_at',
            'needs_transport',
            'status',
        ];
    }

    protected $hidden = [
        'status_index',
    ];

    protected $fillable = [
        'patient_id',
        'doctor_type',
        'provider_name',
        'street',
        'house_number',
        'postal_code',
        'city',
        'starts_at',
        'needs_transport',
        'notes',
        'doctor_visit_summary',
        'cancellation_reason',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $appointment): void {
            if ($appointment->status === null) {
                $appointment->status = AppointmentStatus::SCHEDULED;
            }

            $appointment->syncBlindIndexesForEncryptedEnums();
        });
    }

    protected function blindIndexedEncryptedEnumAttributes(): array
    {
        return [
            'status' => 'status_index',
        ];
    }

    #[Scope]
    protected function whereStatus(Builder $query, AppointmentStatus $status): void
    {
        $query->where('status_index', BlindIndex::forEnum($status));
    }

    /**
     * @param  list<AppointmentStatus>  $statuses
     */
    #[Scope]
    protected function whereStatusIn(Builder $query, array $statuses): void
    {
        $query->whereIn('status_index', array_map(
            BlindIndex::forEnum(...),
            $statuses,
        ));
    }

    protected function casts(): array
    {
        return [
            'doctor_type' => DoctorType::class,
            'provider_name' => 'encrypted',
            'street' => 'encrypted',
            'house_number' => 'encrypted',
            'postal_code' => 'encrypted',
            'city' => 'encrypted',
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
