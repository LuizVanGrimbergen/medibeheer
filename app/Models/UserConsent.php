<?php

namespace App\Models;

use App\Enums\UserConsentType;
use App\Models\Concerns\MaintainsBlindIndexForEncryptedEnum;
use App\Support\BlindIndex;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserConsent extends Model
{
    use MaintainsBlindIndexForEncryptedEnum;

    protected $hidden = [
        'type_index',
    ];

    protected $fillable = [
        'user_id',
        'type',
        'policy_version',
        'accepted_at',
        'ip_address',
    ];

    protected function blindIndexedEncryptedEnumAttributes(): array
    {
        return [
            'type' => 'type_index',
        ];
    }

    #[Scope]
    protected function whereType(Builder $query, UserConsentType $type): void
    {
        $query->where('type_index', BlindIndex::forEnum($type));
    }

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
