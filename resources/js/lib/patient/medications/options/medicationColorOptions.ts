const MEDICATION_COLOR_DEFINITIONS = [
    { value: '#2f6fae', labelKey: 'blue' },
    { value: '#dc2626', labelKey: 'red' },
    { value: '#9333ea', labelKey: 'purple' },
    { value: '#171717', labelKey: 'black' },
    { value: '#db2777', labelKey: 'pink' },
] as const;

export type MedicationColorLabelKey = (typeof MEDICATION_COLOR_DEFINITIONS)[number]['labelKey'];

export type MedicationColorHex = (typeof MEDICATION_COLOR_DEFINITIONS)[number]['value'];

export const MEDICATION_COLOR_HEX_VALUES = MEDICATION_COLOR_DEFINITIONS.map(
    (definition) => definition.value,
) as readonly MedicationColorHex[];

export type MedicationColorOption = {
    value: MedicationColorHex;
    labelKey: MedicationColorLabelKey;
};

export const MEDICATION_COLOR_OPTIONS: MedicationColorOption[] = MEDICATION_COLOR_DEFINITIONS.map(
    (definition) => ({
        value: definition.value,
        labelKey: definition.labelKey,
    }),
);
