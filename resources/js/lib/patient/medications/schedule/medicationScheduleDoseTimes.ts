export const MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES = 30;

export const MEDICATION_SCHEDULE_SNOOZE_MINUTE_OPTIONS = [
    15, 30, 45, 60, 90, 120, 180,
] as const;

export type MedicationScheduleDoseTimeSlot = {
    time: string;
    snoozeMinutes: number;
};

function clampSnoozeMinutes(minutes: number): number {
    if (minutes < 0) {
        return 0;
    }

    if (minutes > 24 * 60) {
        return 24 * 60;
    }

    return minutes;
}

function parseSnoozeMinutes(value: string): number | null {
    const trimmed = value.trim();

    if (trimmed.length < 1 || !/^\d+$/.test(trimmed)) {
        return null;
    }

    return clampSnoozeMinutes(Number(trimmed));
}

export function parseMedicationScheduleDoseTimes(raw: string): MedicationScheduleDoseTimeSlot[] {
    const trimmed = raw.trim();

    if (trimmed.length < 1) {
        return [];
    }

    const slots: MedicationScheduleDoseTimeSlot[] = [];

    for (const segment of trimmed.split(',')) {
        const part = segment.trim();

        if (part.length < 1) {
            continue;
        }

        let time = part;
        let snoozeMinutes = MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES;

        if (part.includes('|')) {
            const [timePart, snoozePart] = part.split('|', 2);
            time = timePart.trim();
            const parsedSnooze = parseSnoozeMinutes(snoozePart ?? '');

            if (parsedSnooze !== null) {
                snoozeMinutes = parsedSnooze;
            }
        }

        if (time.length < 1) {
            continue;
        }

        slots.push({ time, snoozeMinutes });
    }

    return slots;
}

export function buildMedicationScheduleSnoozeTimeSlots(
    doseTimeRaw: string,
    snoozeTimeRaw: string | null | undefined,
    slotCount: number,
): string[] {
    const parsed = parseMedicationScheduleDoseTimes(doseTimeRaw);
    const snoozeSegments =
        snoozeTimeRaw === null || snoozeTimeRaw === undefined
            ? []
            : snoozeTimeRaw.split(',').map((segment) => segment.trim());

    return Array.from({ length: slotCount }, (_, index) => {
        const fromParsed = parsed[index]?.snoozeMinutes;

        if (fromParsed !== undefined) {
            return String(fromParsed);
        }

        const fromSnoozeField = snoozeSegments[index];

        if (fromSnoozeField !== undefined && fromSnoozeField.length > 0) {
            const parsedSnooze = parseSnoozeMinutes(fromSnoozeField);

            return String(parsedSnooze ?? MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES);
        }

        return String(MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES);
    });
}

export function buildMedicationScheduleDoseTimeSlots(
    doseTimeRaw: string,
    slotCount: number,
): string[] {
    const parsed = parseMedicationScheduleDoseTimes(doseTimeRaw);
    const legacySegments = doseTimeRaw
        .split(',')
        .map((segment) => segment.trim())
        .filter((segment) => segment.length > 0);

    return Array.from({ length: slotCount }, (_, index) => {
        const fromParsed = parsed[index]?.time;

        if (fromParsed !== undefined) {
            return fromParsed;
        }

        return legacySegments[index] ?? '';
    });
}

export function buildMedicationScheduleSnoozeTimeForPayload(
    schedule: {
        times_per_day: string;
        snooze_time_slots: readonly string[];
    },
    slotCount: number,
): string {
    return schedule.snooze_time_slots
        .slice(0, slotCount)
        .map((slot) => {
            const parsed = parseSnoozeMinutes(slot);

            return String(parsed ?? MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES);
        })
        .join(', ');
}
