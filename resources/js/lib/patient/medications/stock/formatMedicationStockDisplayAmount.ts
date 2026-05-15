import type { ComposerTranslation } from 'vue-i18n';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { medicationStockSupportsUnitSuffix } from '@/lib/patient/medications/stock/medicationStockSupportsUnitSuffix';
import { medicationStockValueHasUnitSuffix } from '@/lib/patient/medications/stock/medicationStockUnitSuffixPattern';

export function formatMedicationStockDisplayAmount(
    t: ComposerTranslation,
    amount: string,
    doseUnit: string | null,
): string {
    const trimmed = amount.trim();

    if (trimmed.length < 1 || doseUnit === null) {
        return trimmed;
    }

    if (!medicationStockSupportsUnitSuffix(doseUnit)) {
        return trimmed;
    }

    if (medicationStockValueHasUnitSuffix(trimmed, doseUnit)) {
        return trimmed;
    }

    const chip = medicationDoseUnitChipForAmount(t, trimmed, doseUnit);

    if (chip === '—') {
        return trimmed;
    }

    return `${trimmed} ${chip}`;
}
