<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\BlindIndex;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

/** @mixin Model */
trait MaintainsBlindIndexForEncryptedEnum
{
    protected static function bootMaintainsBlindIndexForEncryptedEnum(): void
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::class;

        $modelClass::saving(function (Model $model): void {
            $model->syncBlindIndexesForEncryptedEnums();
        });
    }

    public function syncBlindIndexesForEncryptedEnums(): void
    {
        foreach ($this->blindIndexedEncryptedEnumAttributes() as $attribute => $indexColumn) {
            if (! $this->isDirty($attribute) && $this->getAttribute($indexColumn) !== null) {
                continue;
            }

            $value = $this->getAttribute($attribute);

            if ($value instanceof BackedEnum) {
                $this->setAttribute($indexColumn, BlindIndex::forEnum($value));

                continue;
            }

            $this->setAttribute($indexColumn, null);
        }
    }

    /**
     * @return array<string, string>
     */
    abstract protected function blindIndexedEncryptedEnumAttributes(): array;
}
