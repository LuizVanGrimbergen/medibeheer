import type { ComposerTranslation } from 'vue-i18n';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import type { MedicationDoseUnitValue, MedicationTypeValue } from '@/lib/types';

export type MedicationIntakeSlotDisplaySource = {
    dose: string | null;
    dose_unit: MedicationDoseUnitValue | null;
    note: string | null;
    type_medication: MedicationTypeValue;
};

export function medicationIntakeDoseLine(
    t: ComposerTranslation,
    source: MedicationIntakeSlotDisplaySource,
): string | null {
    const dose = source.dose?.trim();

    if (dose === undefined || dose === null || dose.length < 1) {
        return null;
    }

    const unit = source.dose_unit;

    if (unit === null) {
        return dose;
    }

    const chip = medicationDoseUnitChipForAmount(t, dose, unit);

    return `${dose} ${chip}`;
}

export function medicationIntakeNotePreview(source: { note: string | null }): string | null {
    const raw = source.note;

    if (raw === null) {
        return null;
    }

    const trimmed = raw.trim();

    if (trimmed.length < 1) {
        return null;
    }

    return trimmed;
}

export function medicationTypeLabel(
    t: ComposerTranslation,
    typeMedication: MedicationTypeValue,
): string {
    return t(`patient.medications.types.${typeMedication}`);
}
