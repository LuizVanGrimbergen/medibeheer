export const MEDICATION_STRENGTH_UNIT_VALUES = [
    'milligram',
    'gram',
    'milliliter',
] as const;

export type MedicationStrengthUnitValue =
    (typeof MEDICATION_STRENGTH_UNIT_VALUES)[number];

export type MedicationStrengthUnitOption = {
    value: MedicationStrengthUnitValue;
    labelKey: MedicationStrengthUnitValue;
};

export const MEDICATION_STRENGTH_UNIT_OPTIONS: MedicationStrengthUnitOption[] =
    MEDICATION_STRENGTH_UNIT_VALUES.map((value) => ({
        value,
        labelKey: value,
    }));

const STRENGTH_UNIT_STORAGE_LABEL: Record<MedicationStrengthUnitValue, string> =
    {
        milligram: 'mg',
        gram: 'g',
        milliliter: 'ml',
    };

export function medicationStrengthUnitStorageLabel(
    unit: MedicationStrengthUnitValue,
): string {
    return STRENGTH_UNIT_STORAGE_LABEL[unit];
}
