import type { MedicationDoseUnitValue } from '@/lib/types';
import { MEDICATION_DOSE_UNIT_FORM_VALUES } from '@/lib/patient/medications/options/medicationDoseUnitForm';

export type MedicationDoseUnitOption = {
    value: MedicationDoseUnitValue;
    labelKey: MedicationDoseUnitValue;
};

export const MEDICATION_DOSE_UNIT_OPTIONS: MedicationDoseUnitOption[] =
    MEDICATION_DOSE_UNIT_FORM_VALUES.map((value) => ({
        value,
        labelKey: value,
    }));

export function medicationDoseUnitOptionsForSelect(
    currentDoseUnit: MedicationDoseUnitValue | '',
): MedicationDoseUnitOption[] {
    if (
        currentDoseUnit !== '' &&
        currentDoseUnit === 'unit'
    ) {
        return [
            ...MEDICATION_DOSE_UNIT_OPTIONS,
            { value: 'unit', labelKey: 'unit' },
        ];
    }

    return MEDICATION_DOSE_UNIT_OPTIONS;
}
