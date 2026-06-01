import type { MedicationDoseUnitValue, MedicationTypeValue } from '@/lib/types';

/** Suggested dose unit when the user picks a form type and has not chosen a unit yet. */
export function defaultDoseUnitForMedicationType(
    type: MedicationTypeValue | '',
): MedicationDoseUnitValue | null {
    if (type === '') {
        return null;
    }

    if (type === 'pill') {
        return 'piece';
    }

    if (type === 'liquid') {
        return 'milliliter';
    }

    if (type === 'injection') {
        return 'piece';
    }

    if (type === 'sachets') {
        return 'piece';
    }

    return null;
}
