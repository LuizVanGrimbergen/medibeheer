import type { Composer } from 'vue-i18n';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { medicationStockDisplayDoseUnit } from '@/lib/patient/medications/stock/medicationStockDisplayDoseUnit';
import type { MedicationDoseUnitValue } from '@/lib/types';
import { MEDICATION_DOSE_UNIT_VALUES } from '@/lib/types';

export function inventoryVacationDoseUnitForItem(
    doseUnit: string | null,
): string | null {
    return medicationStockDisplayDoseUnit(doseUnit, null);
}

export function inventoryVacationDoseUnitChipForItem(
    t: Composer['t'],
    amount: string,
    doseUnit: string | null,
): string | null {
    if (doseUnit === null || doseUnit === '') {
        return null;
    }

    if (
        !(MEDICATION_DOSE_UNIT_VALUES as readonly string[]).includes(doseUnit)
    ) {
        return null;
    }

    const chip = medicationDoseUnitChipForAmount(
        t,
        amount,
        doseUnit as MedicationDoseUnitValue,
    );

    if (chip === '—') {
        return null;
    }

    return chip;
}
