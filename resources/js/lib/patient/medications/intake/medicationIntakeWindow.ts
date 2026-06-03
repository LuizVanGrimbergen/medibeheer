const MEDICATION_INTAKE_TIMEZONE = 'Europe/Amsterdam';

type MedicationIntakeDateTimeParts = {
    year: number;
    month: number;
    day: number;
    hour: number;
    minute: number;
};

function medicationIntakeDateTimeParts(
    reference: Date,
): MedicationIntakeDateTimeParts {
    const formatter = new Intl.DateTimeFormat('en-GB', {
        timeZone: MEDICATION_INTAKE_TIMEZONE,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });

    const parts = formatter.formatToParts(reference);
    const value = (type: Intl.DateTimeFormatPartTypes): number =>
        Number(parts.find((part) => part.type === type)?.value ?? '0');

    return {
        year: value('year'),
        month: value('month'),
        day: value('day'),
        hour: value('hour'),
        minute: value('minute'),
    };
}

export function buildMedicationTakenAtForToday(
    timeHHmm: string,
    reference: Date = new Date(),
): string | null {
    const match = /^(\d{1,2}):(\d{2})$/.exec(timeHHmm.trim());

    if (match === null) {
        return null;
    }

    const hours = Number(match[1]);
    const minutes = Number(match[2]);

    if (hours > 23 || minutes > 59) {
        return null;
    }

    const { year, month, day } = medicationIntakeDateTimeParts(reference);

    return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')} ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:00`;
}

export function currentMedicationIntakeTimeHHmm(
    reference: Date = new Date(),
): string {
    const { hour, minute } = medicationIntakeDateTimeParts(reference);

    return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
}
