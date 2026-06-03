import {
    medicationStrengthUnitStorageLabel
    
} from '@/lib/patient/medications/options/medicationStrengthUnitForm';
import type {MedicationStrengthUnitValue} from '@/lib/patient/medications/options/medicationStrengthUnitForm';

export function medicationStrengthUnitChipForAmount(
    unit: MedicationStrengthUnitValue,
): string {
    return medicationStrengthUnitStorageLabel(unit);
}
