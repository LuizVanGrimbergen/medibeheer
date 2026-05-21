export const MEDICATION_SCHEDULE_DEFAULT_SNOOZE_MINUTES = 30;

export const MEDICATION_SCHEDULE_SNOOZE_MINUTE_OPTIONS = [
    15, 30, 45, 60, 90, 120, 180,
] as const;

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

function parseDoseTimeSegments(doseTimeRaw: string): string[] {
    const trimmed = doseTimeRaw.trim();

    if (trimmed.length < 1) {
        return [];
    }

    return trimmed
        .split(',')
        .map((segment) => segment.trim())
        .filter((segment) => segment.length > 0);
}

function parseSnoozeTimeSegments(snoozeTimeRaw: string): string[] {
    const trimmed = snoozeTimeRaw.trim();

    if (trimmed.length < 1) {
        return [];
    }

    return trimmed
        .split(',')
        .map((segment) => segment.trim())
        .filter((segment) => segment.length > 0);
}

export function buildMedicationScheduleSnoozeTimeSlots(
    snoozeTimeRaw: string | null | undefined,
    slotCount: number,
): string[] {
    const snoozeSegments =
        snoozeTimeRaw === null || snoozeTimeRaw === undefined
            ? []
            : parseSnoozeTimeSegments(snoozeTimeRaw);

    return Array.from({ length: slotCount }, (_, index) => {
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
    const parsed = parseDoseTimeSegments(doseTimeRaw);

    return Array.from({ length: slotCount }, (_, index) => parsed[index] ?? '');
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
