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

const DOSE_UNITS_HIDDEN_UNLESS_SELECTED = ['unit', 'drop'] as const satisfies readonly MedicationDoseUnitValue[];

export function medicationDoseUnitOptionsForSelect(
    currentDoseUnit: MedicationDoseUnitValue | '',
): MedicationDoseUnitOption[] {
    const hiddenSelected = DOSE_UNITS_HIDDEN_UNLESS_SELECTED.filter(
        (value) => currentDoseUnit === value,
    ).map((value) => ({ value, labelKey: value }));

    if (hiddenSelected.length > 0) {
        return [...MEDICATION_DOSE_UNIT_OPTIONS, ...hiddenSelected];
    }

    return MEDICATION_DOSE_UNIT_OPTIONS;
}
