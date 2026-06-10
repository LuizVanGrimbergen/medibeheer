<?php

namespace App\Models;

use App\Enums\MedicationPlanProposalStatus;
use App\Models\Concerns\MaintainsBlindIndexForEncryptedEnum;
use App\Support\BlindIndex;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationPlanProposal extends Model
{
    use HasFactory;
    use MaintainsBlindIndexForEncryptedEnum;

    protected $hidden = [
        'status_index',
    ];

    protected $fillable = [
        'patient_id',
        'family_id',
        'invited_patient_email',
        'invited_patient_email_hash',
        'status',
        'token_hash',
        'expires_at',
        'published_at',
        'accepted_at',
        'declined_at',
        'revoked_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $proposal): void {
            if ($proposal->status === null) {
                $proposal->status = MedicationPlanProposalStatus::DRAFT;
            }

            $proposal->syncBlindIndexesForEncryptedEnums();
        });
    }

    protected function blindIndexedEncryptedEnumAttributes(): array
    {
        return [
            'status' => 'status_index',
        ];
    }

    #[Scope]
    protected function whereStatus(Builder $query, MedicationPlanProposalStatus $status): void
    {
        $query->where('status_index', BlindIndex::forEnum($status));
    }

    protected function casts(): array
    {
        return [
            'invited_patient_email' => 'encrypted',
            'status' => MedicationPlanProposalStatus::class,
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MedicationPlanProposalItem::class)->orderBy('sort_order');
    }

    public function isDraft(): bool
    {
        return $this->status === MedicationPlanProposalStatus::DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === MedicationPlanProposalStatus::PUBLISHED;
    }

    public function matchesInvitedUserEmail(User $user): bool
    {
        if ($this->invited_patient_email_hash === null) {
            return true;
        }

        foreach (User::emailHashCandidates($user->email) as $candidate) {
            if (hash_equals($this->invited_patient_email_hash, $candidate)) {
                return true;
            }
        }

        return false;
    }

    public function isRedeemable(): bool
    {
        if ($this->status !== MedicationPlanProposalStatus::PUBLISHED) {
            return false;
        }

        if ($this->accepted_at !== null || $this->declined_at !== null || $this->revoked_at !== null) {
            return false;
        }

        if ($this->token_hash === null) {
            return false;
        }

        $expiresAt = $this->expires_at;

        return $expiresAt !== null && $expiresAt->isFuture();
    }

    #[Scope]
    protected function redeemable(Builder $query): Builder
    {
        return $query
            ->whereStatus(MedicationPlanProposalStatus::PUBLISHED)
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->whereNull('revoked_at')
            ->whereNotNull('token_hash')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now());
    }
}
