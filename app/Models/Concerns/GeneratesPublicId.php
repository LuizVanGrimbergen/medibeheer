<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/** @mixin Model */
trait GeneratesPublicId
{
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    protected static function bootGeneratesPublicId(): void
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::class;

        $modelClass::creating(function (Model $model): void {
            if ($model->public_id !== null) {
                return;
            }

            $model->public_id = (string) str()->uuid();
        });
    }
}
