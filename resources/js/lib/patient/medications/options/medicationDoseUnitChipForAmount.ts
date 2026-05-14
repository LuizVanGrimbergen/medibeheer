import type { ComposerTranslation } from 'vue-i18n';
import { MEDICATION_DOSE_UNIT_VALUES } from '@/lib/types';
import type { MedicationDoseUnitValue } from '@/lib/types';

export function parseMedicationDoseNumericCount(dose: string): number | null {
    const trimmed = dose.trim();

    if (trimmed.length < 1) {
        return null;
    }

    const normalized = trimmed.replace(',', '.');
    const n = Number.parseFloat(normalized);

    if (!Number.isFinite(n)) {
        return null;
    }

    return n;
}

export function medicationDoseUnitChipForAmount(
    t: ComposerTranslation,
    doseAmount: string,
    doseUnit: string,
): string {
    if (doseUnit === '' || !(MEDICATION_DOSE_UNIT_VALUES as readonly string[]).includes(doseUnit)) {
        return '—';
    }

    const unit = doseUnit as MedicationDoseUnitValue;
    const count = parseMedicationDoseNumericCount(doseAmount);
    const usePlural = count !== null && Math.abs(count) !== 1;

    if (usePlural) {
        return t(`patient.medications.doseUnitLabels.${unit}.chipPlural`);
    }

    return t(`patient.medications.doseUnitLabels.${unit}.chip`);
}
