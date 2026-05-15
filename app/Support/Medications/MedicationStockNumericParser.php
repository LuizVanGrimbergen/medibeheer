<?php

declare(strict_types=1);

namespace App\Support\Medications;

use App\Enums\MedicationDoseUnit;

final class MedicationStockNumericParser
{
    public function parse(string $value, ?MedicationDoseUnit $doseUnit = null): ?float
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        if ($doseUnit !== null) {
            $trimmed = $this->stripUnitSuffix($trimmed, $doseUnit);
        }

        if ($trimmed === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $trimmed);

        if (! is_numeric($normalized)) {
            return null;
        }

        $float = (float) $normalized;

        if (! is_finite($float) || $float < 0.0) {
            return null;
        }

        return $float;
    }

    private function stripUnitSuffix(string $value, MedicationDoseUnit $doseUnit): string
    {
        $pattern = match ($doseUnit) {
            MedicationDoseUnit::MILLIGRAM => '/\s*(?:mg|milligram(?:men)?)\s*$/iu',
            MedicationDoseUnit::GRAM => '/\s*(?:g|gram(?:men)?)\s*$/iu',
            MedicationDoseUnit::MILLILITER => '/\s*(?:ml|milliliter)\s*$/iu',
            MedicationDoseUnit::PIECE => '/\s*(?:stuk|stuks)\s*$/iu',
            MedicationDoseUnit::DROP => '/\s*(?:druppel|druppels)\s*$/iu',
            MedicationDoseUnit::INJECTION => '/\s*(?:injectie|injecties)\s*$/iu',
            MedicationDoseUnit::UNIT => '/\s*(?:eenheid|eenheden)\s*$/iu',
            MedicationDoseUnit::SACHET => '/\s*(?:zakje|zakjes)\s*$/iu',
            MedicationDoseUnit::OTHER => null,
        };

        if ($pattern === null) {
            return $value;
        }

        return trim((string) preg_replace($pattern, '', $value));
    }
}
