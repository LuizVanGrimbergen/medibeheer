import type { ComposerTranslation } from 'vue-i18n';
import { parseMedicationDoseNumericCount } from '@/lib/patient/medications/validation/medicationFormValidationPrimitives';
import type { MedicationDoseUnitValue } from '@/lib/types';
import { MEDICATION_DOSE_UNIT_VALUES } from '@/lib/types';

export function medicationDoseUnitChipForAmount(
    t: ComposerTranslation,
    doseAmount: string,
    doseUnit: string,
): string {
    if (
        doseUnit === '' ||
        !(MEDICATION_DOSE_UNIT_VALUES as readonly string[]).includes(doseUnit)
    ) {
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
