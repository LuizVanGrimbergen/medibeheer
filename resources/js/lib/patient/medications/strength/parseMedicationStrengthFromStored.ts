import type { MedicationStrengthUnitValue } from '@/lib/patient/medications/options/medicationStrengthUnitForm';
import { MEDICATION_STRENGTH_UNIT_VALUES } from '@/lib/patient/medications/options/medicationStrengthUnitForm';

const STORAGE_LABEL_TO_UNIT: Record<string, MedicationStrengthUnitValue> = {
    mg: 'milligram',
    g: 'gram',
    ml: 'milliliter',
};

export function parseMedicationStrengthFromStored(
    strength: string | null | undefined,
): {
    strength: string;
    strength_amount: string;
    strength_unit: MedicationStrengthUnitValue | '';
} {
    const trimmed = strength?.trim() ?? '';

    if (trimmed.length < 1) {
        return {
            strength: '',
            strength_amount: '',
            strength_unit: '',
        };
    }

    const match = /^([\d.,]+)\s*(mg|g|ml)\b/i.exec(trimmed);

    if (match === null) {
        return {
            strength: trimmed,
            strength_amount: '',
            strength_unit: '',
        };
    }

    const unit = STORAGE_LABEL_TO_UNIT[match[2].toLowerCase()] ?? '';

    if (
        !(MEDICATION_STRENGTH_UNIT_VALUES as readonly string[]).includes(unit)
    ) {
        return {
            strength: trimmed,
            strength_amount: '',
            strength_unit: '',
        };
    }

    return {
        strength: trimmed,
        strength_amount: match[1],
        strength_unit: unit,
    };
}
