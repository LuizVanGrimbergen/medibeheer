<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait ScopesPendingPatientCareTeamInvitation
{
    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }

    public function isPending(): bool
    {
        if ($this->accepted_at !== null || $this->revoked_at !== null) {
            return false;
        }

        return $this->expires_at->isFuture();
    }
}
