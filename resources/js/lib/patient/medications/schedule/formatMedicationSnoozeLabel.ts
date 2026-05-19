import type { ComposerTranslation } from 'vue-i18n';

export function formatMedicationSnoozeMinutesLabel(
    t: ComposerTranslation,
    minutes: number,
): string {
    if (minutes < 60) {
        return t('patient.medications.snoozeTime.minutes', { count: minutes });
    }

    if (minutes % 60 !== 0) {
        return t('patient.medications.snoozeTime.minutes', { count: minutes });
    }

    const hours = minutes / 60;

    return hours === 1
        ? t('patient.medications.snoozeTime.oneHour')
        : t('patient.medications.snoozeTime.hours', { count: hours });
}

export function formatMedicationSnoozeMinutesLabelFromRaw(
    t: ComposerTranslation,
    minutesRaw: string,
    emptyPlaceholder = '—',
): string {
    const minutes = Number(minutesRaw.trim());

    if (!Number.isInteger(minutes) || minutes < 1) {
        return emptyPlaceholder;
    }

    return formatMedicationSnoozeMinutesLabel(t, minutes);
}
