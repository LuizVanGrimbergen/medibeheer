export const PATIENT_FORM_COUNT_PRESET_VALUES = [1, 2, 3, 4] as const;

export type PatientFormCountPresetValue =
    (typeof PATIENT_FORM_COUNT_PRESET_VALUES)[number];

export function isPatientFormCountPresetValue(count: number): boolean {
    return (PATIENT_FORM_COUNT_PRESET_VALUES as readonly number[]).includes(count);
}

export function patientFormCountCustomOptions(
    customMin: number,
    customMax: number,
): number[] {
    return Array.from(
        { length: customMax - customMin + 1 },
        (_, index) => customMin + index,
    );
}

export function isPatientFormCountCustomValue(
    count: number,
    customMin: number,
    customMax: number,
): boolean {
    if (!Number.isInteger(count)) {
        return false;
    }

    return count >= customMin && count <= customMax;
}
