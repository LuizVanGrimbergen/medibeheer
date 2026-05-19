import type { ComposerTranslation } from 'vue-i18n';
import type { MedicationIntakeFrequencyValue } from '@/lib/types';

export const MEDICATION_INTAKE_FREQUENCY_WIZARD_PRESETS = [
    'daily',
    'every_2_days',
    'every_3_days',
] as const satisfies readonly MedicationIntakeFrequencyValue[];

export const MEDICATION_INTAKE_FREQUENCY_SUMMARY_OPTION_PRESETS = [
    ...MEDICATION_INTAKE_FREQUENCY_WIZARD_PRESETS,
    'every_4_days',
] as const satisfies readonly MedicationIntakeFrequencyValue[];

export const MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MIN = 5;

export const MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MAX = 60;

export const MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_OPTIONS = Array.from(
    { length: MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MAX - MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MIN + 1 },
    (_, index) => index + MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MIN,
);

const EVERY_N_DAYS_PATTERN = /^every_(\d+)_days$/;

export function parseEveryNDaysFrequency(frequency: string): number | null {
    const match = EVERY_N_DAYS_PATTERN.exec(frequency);

    if (match === null) {
        return null;
    }

    const dayCount = Number(match[1]);

    if (!Number.isInteger(dayCount) || dayCount < 2 || dayCount > 60) {
        return null;
    }

    return dayCount;
}

export function isCustomIntakeFrequencyInterval(frequency: string): boolean {
    const dayCount = parseEveryNDaysFrequency(frequency);

    if (dayCount === null) {
        return false;
    }

    return (
        dayCount >= MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MIN &&
        dayCount <= MEDICATION_INTAKE_FREQUENCY_CUSTOM_DAY_MAX
    );
}

export function isWizardIntakeFrequencyPreset(frequency: string): boolean {
    return (MEDICATION_INTAKE_FREQUENCY_WIZARD_PRESETS as readonly string[]).includes(frequency);
}

export function medicationIntakeFrequencyLabel(
    t: ComposerTranslation,
    frequency: MedicationIntakeFrequencyValue,
): string {
    if (frequency === 'daily') {
        return t('patient.medications.intakeFrequencies.daily');
    }

    if (frequency === 'weekdays') {
        return t('patient.medications.intakeFrequencies.weekdays');
    }

    const dayCount = parseEveryNDaysFrequency(frequency);

    if (dayCount === null) {
        return frequency;
    }

    return t('patient.medications.intakeFrequencies.everyNDays', { n: dayCount });
}

export function medicationIntakeFrequencySummaryLabel(
    t: ComposerTranslation,
    frequency: MedicationIntakeFrequencyValue | '',
    intakeWeekdays: readonly number[],
    emptyPlaceholder = '—',
): string {
    if (frequency === '') {
        return emptyPlaceholder;
    }

    if (frequency === 'weekdays') {
        const baseLabel = medicationIntakeFrequencyLabel(t, frequency);

        if (intakeWeekdays.length < 1) {
            return baseLabel;
        }

        const parts = intakeWeekdays.map((day) => t(`patient.medications.weekdayIso.${day}`));

        return `${baseLabel} (${parts.join(', ')})`;
    }

    return medicationIntakeFrequencyLabel(t, frequency);
}
