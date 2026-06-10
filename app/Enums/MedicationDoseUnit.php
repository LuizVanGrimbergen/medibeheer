<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;

enum MedicationDoseUnit: string implements Castable
{
    use EncryptEnum;

    case MILLILITER = 'milliliter';
    case PIECE = 'piece';
    case DROP = 'drop';
    case UNIT = 'unit';

    public function requiresStrength(): bool
    {
        return $this === self::DROP;
    }

    public static function selectableForForms(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $unit): bool => ! in_array($unit, [self::UNIT, self::DROP], true),
        ));
    }
}
