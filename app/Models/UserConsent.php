<?php

namespace App\Models;

use App\Enums\UserConsentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserConsent extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'policy_version',
        'accepted_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'type' => UserConsentType::class,
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
