import { useI18n } from 'vue-i18n';

const dateOnlyDisplay = new Intl.DateTimeFormat('nl-NL', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const timeOnlyDisplay = new Intl.DateTimeFormat('nl-NL', {
    hour: '2-digit',
    minute: '2-digit',
});

export function resolveAppointmentDoctorTypeLabel(
    t: (key: string) => string,
    te: (key: string) => boolean,
    type: string,
): string {
    const key = `patient.appointments.doctorTypes.${type}`;

    if (! te(key)) {
        return t('patient.appointments.doctorTypes.fallback');
    }

    return t(key);
}

export function useAppointmentDisplay(): {
    formatDateOnly: (iso: string) => string;
    formatTimeOnly: (iso: string) => string;
    doctorTypeLabel: (type: string) => string;
} {
    const { t, te } = useI18n();

    return {
        formatDateOnly: (iso: string) =>
            dateOnlyDisplay.format(new Date(iso)),
        formatTimeOnly: (iso: string) =>
            timeOnlyDisplay.format(new Date(iso)),
        doctorTypeLabel: (type: string) =>
            resolveAppointmentDoctorTypeLabel(t, te, type),
    };
}
