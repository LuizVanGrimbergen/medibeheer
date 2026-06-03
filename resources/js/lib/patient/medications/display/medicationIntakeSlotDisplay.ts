import type { ComposerTranslation } from 'vue-i18n';
import { medicationDoseUnitChipForAmount } from '@/lib/patient/medications/options/medicationDoseUnitChipForAmount';
import { medicationIntakeFrequencySummaryLabel } from '@/lib/patient/medications/schedule/medicationIntakeFrequencyDisplay';
import type {
    MedicationDoseUnitValue,
    MedicationIntakeFrequencyValue,
    MedicationMealTimingValue,
    MedicationTypeValue,
} from '@/lib/types';

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

export function medicationIntakeNotePreview(source: {
    note: string | null;
}): string | null {
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

export function medicationMealTimingLabel(
    t: ComposerTranslation,
    mealTiming: MedicationMealTimingValue,
): string {
    return t(`patient.medications.mealTimings.${mealTiming}`);
}

export type MedicationTodayIntakeHeaderSource = {
    meal_timing: MedicationMealTimingValue;
    intake_frequency: MedicationIntakeFrequencyValue;
    intake_weekdays: number[] | null;
};

export function medicationTodayIntakeHeaderSummary(
    t: ComposerTranslation,
    source: MedicationTodayIntakeHeaderSource,
): string {
    return [
        medicationMealTimingLabel(t, source.meal_timing),
        medicationIntakeFrequencySummaryLabel(
            t,
            source.intake_frequency,
            source.intake_weekdays ?? [],
        ),
    ].join(' • ');
}

export function medicationCardHeaderSummary(
    t: ComposerTranslation,
    source: MedicationIntakeSlotDisplaySource & {
        doseTimes: readonly string[];
    },
): string {
    const dosePart = medicationIntakeDoseLine(t, source);
    const times = source.doseTimes
        .map((time) => time.trim())
        .filter((time) => time.length > 0);

    const parts: string[] = [];

    if (dosePart !== null) {
        parts.push(dosePart);
    }

    if (times.length > 0) {
        parts.push(times.join(', '));
    }

    if (parts.length > 0) {
        return parts.join(' · ');
    }

    return medicationTypeLabel(t, source.type_medication);
}
