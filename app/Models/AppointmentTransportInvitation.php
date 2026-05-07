<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentTransportInvitation extends Model
{
    use HasFactory;

    /**************************************/
    /*             Attributes */
    /**************************************/
    protected $fillable = [
        'appointment_id',
        'family_id',
        'invited_at',
        'accepted_at',
        'declined_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
    /**************************************/
    /*           Relationships */
    /**************************************/

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**************************************/
    /*           Scopes */
    /**************************************/

    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->whereNull('cancelled_at');
    }
    /**************************************/
    /*           Methods */
    /**************************************/

    public function isPending(): bool
    {
        return $this->accepted_at === null
            && $this->declined_at === null
            && $this->cancelled_at === null;
    }
}
