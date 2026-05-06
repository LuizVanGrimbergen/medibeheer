<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyInvitation extends Model
{
    use HasFactory;

    /**************************************/
    /*             Attributes */
    /**************************************/

    protected $fillable = [
        'patient_id',
        'invited_email',
        'invited_email_hash',
        'token_hash',
        'expires_at',
        'accepted_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_email' => 'encrypted',
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
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

    public function isPending(): bool
    {
        if ($this->accepted_at !== null || $this->revoked_at !== null) {
            return false;
        }

        return $this->expires_at->isFuture();
    }
}
