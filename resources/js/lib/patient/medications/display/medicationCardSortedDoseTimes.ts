import type { MedicationRegisterItem } from '@/lib/types';

function minutesSinceMidnight(value: string): number | null {
    const match = /^(\d{1,2}):(\d{2})$/.exec(value.trim());

    if (match === null) {
        return null;
    }

    const hours = Number(match[1]);
    const minutes = Number(match[2]);

    if (
        Number.isNaN(hours) ||
        Number.isNaN(minutes) ||
        hours > 23 ||
        minutes > 59
    ) {
        return null;
    }

    return hours * 60 + minutes;
}

export function medicationCardSortedDoseTimes(
    medication: MedicationRegisterItem,
): string[] {
    const seen = new Set<string>();

    for (const schedule of medication.schedules) {
        const raw = schedule.dose_time?.trim();

        if (raw === undefined || raw.length < 1) {
            continue;
        }

        for (const segment of raw.split(',')) {
            const part = segment.trim();

            if (part.length > 0) {
                seen.add(part);
            }
        }
    }

    return Array.from(seen).sort((a, b) => {
        const left = minutesSinceMidnight(a);
        const right = minutesSinceMidnight(b);

        if (left === null && right === null) {
            return a.localeCompare(b);
        }

        if (left === null) {
            return 1;
        }

        if (right === null) {
            return -1;
        }

        return left - right;
    });
}
