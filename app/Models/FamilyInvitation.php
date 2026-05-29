<?php

namespace App\Models;

use App\Models\Concerns\GeneratesPublicId;
use App\Models\Concerns\ScopesPendingPatientCareTeamInvitation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyInvitation extends Model
{
    use GeneratesPublicId;
    use HasFactory;
    use ScopesPendingPatientCareTeamInvitation;

    protected $fillable = [
        'patient_id',
        'invited_email',
        'invited_email_hash',
        'expires_at',
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

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
