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
}
