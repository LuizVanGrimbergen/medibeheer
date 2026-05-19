<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\User;
use App\Support\InertiaPagination;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

final class UserSecurityActivityScreenService
{
    public function paginatedForUser(User $user): array
    {
        $paginator = Activity::query()
            ->where('log_name', ActivityLogName::SECURITY)
            ->where(function (Builder $query) use ($user): void {
                $query->where(function (Builder $causerQuery) use ($user): void {
                    $causerQuery
                        ->where('causer_type', $user->getMorphClass())
                        ->where('causer_id', $user->id);
                })->orWhere(function (Builder $subjectQuery) use ($user): void {
                    $subjectQuery
                        ->where('subject_type', $user->getMorphClass())
                        ->where('subject_id', $user->id);
                });
            })
            ->latest('id')
            ->paginate(InertiaPagination::PER_PAGE)
            ->withQueryString();

        $entries = collect($paginator->items())
            ->map(fn (Activity $activity): array => $this->mapEntry($activity))
            ->all();

        return InertiaPagination::payload($paginator, $entries);
    }

    private function mapEntry(Activity $activity): array
    {
        $properties = $activity->properties->toArray();

        return [
            'id' => (int) $activity->id,
            'description' => (string) $activity->description,
            'created_at' => $activity->created_at?->toIso8601String(),
            'ip' => isset($properties['ip']) ? (string) $properties['ip'] : null,
        ];
    }
}
