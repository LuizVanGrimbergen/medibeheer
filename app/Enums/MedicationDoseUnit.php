<?php

namespace App\Enums;

enum MedicationDoseUnit: string
{
    case MILLIGRAM = 'milligram';
    case GRAM = 'gram';
    case MILLILITER = 'milliliter';
    case PIECE = 'piece';
    case DROP = 'drop';
    case INJECTION = 'injection';
    case UNIT = 'unit';
    case SACHET = 'sachet';
    case OTHER = 'other';

    public function requiresStrength(): bool
    {
        return $this === self::DROP || $this === self::INJECTION;
    }

    public static function selectableForForms(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $unit): bool => $unit !== self::UNIT,
        ));
    }
}
