import type { MedicationStockUnitSuffixDoseUnit } from '@/lib/patient/medications/stock/medicationStockSupportsUnitSuffix';

const UNIT_SUFFIX_PATTERNS: Record<MedicationStockUnitSuffixDoseUnit, RegExp> = {
    milligram: /\s*(?:mg|milligram(?:men)?)\s*$/iu,
    gram: /\s*(?:g|gram(?:men)?)\s*$/iu,
    milliliter: /\s*(?:ml|milliliter)\s*$/iu,
    piece: /\s*(?:stuk|stuks)\s*$/iu,
    drop: /\s*(?:druppel|druppels)\s*$/iu,
    injection: /\s*(?:injectie|injecties)\s*$/iu,
    unit: /\s*(?:eenheid|eenheden)\s*$/iu,
    sachet: /\s*(?:zakje|zakjes)\s*$/iu,
};

export function medicationStockUnitSuffixPattern(
    doseUnit: MedicationStockUnitSuffixDoseUnit,
): RegExp {
    return UNIT_SUFFIX_PATTERNS[doseUnit];
}

export function medicationStockValueHasUnitSuffix(
    value: string,
    doseUnit: MedicationStockUnitSuffixDoseUnit,
): boolean {
    const trimmed = value.trim();

    if (trimmed.length < 1) {
        return false;
    }

    return medicationStockUnitSuffixPattern(doseUnit).test(trimmed);
}
