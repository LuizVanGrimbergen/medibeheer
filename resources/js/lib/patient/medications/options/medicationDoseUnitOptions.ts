import type { MedicationDoseUnitValue } from '@/lib/types';
import { MEDICATION_DOSE_UNIT_VALUES } from '@/lib/types';

export type MedicationDoseUnitOption = {
    value: MedicationDoseUnitValue;
    labelKey: MedicationDoseUnitValue;
};

export const MEDICATION_DOSE_UNIT_OPTIONS: MedicationDoseUnitOption[] =
    MEDICATION_DOSE_UNIT_VALUES.map((value) => ({
        value,
        labelKey: value,
    }));
