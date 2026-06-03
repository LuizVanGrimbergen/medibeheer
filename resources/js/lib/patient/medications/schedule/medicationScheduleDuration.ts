export function todayLocalIsoDate(): string {
    const d = new Date();
    const z = (n: number) => String(n).padStart(2, '0');

    return `${d.getFullYear()}-${z(d.getMonth() + 1)}-${z(d.getDate())}`;
}

function addDaysToYmdLocal(startYmd: string, daysToAdd: number): string | null {
    const trimmed = startYmd.trim();
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(trimmed);

    if (match === null) {
        return null;
    }

    const year = Number(match[1]);
    const month = Number(match[2]);
    const day = Number(match[3]);
    const d = new Date(year, month - 1, day);

    if (
        d.getFullYear() !== year ||
        d.getMonth() !== month - 1 ||
        d.getDate() !== day
    ) {
        return null;
    }

    d.setDate(d.getDate() + daysToAdd);
    const z = (n: number) => String(n).padStart(2, '0');

    return `${d.getFullYear()}-${z(d.getMonth() + 1)}-${z(d.getDate())}`;
}

export function medicationScheduleEndDateIsoInclusiveLocal(
    startYmd: string,
    inclusiveDurationDays: number,
): string | null {
    if (!Number.isInteger(inclusiveDurationDays) || inclusiveDurationDays < 1) {
        return null;
    }

    return addDaysToYmdLocal(startYmd, inclusiveDurationDays - 1);
}

export function inclusiveCalendarDaysBetweenIsoDates(
    startYmd: string,
    endYmd: string,
): number | null {
    const startTrimmed = startYmd.trim();
    const endTrimmed = endYmd.trim();

    if (medicationScheduleEndDateIsoInclusiveLocal(startTrimmed, 1) === null) {
        return null;
    }

    if (medicationScheduleEndDateIsoInclusiveLocal(endTrimmed, 1) === null) {
        return null;
    }

    const startMatch = /^(\d{4})-(\d{2})-(\d{2})$/.exec(startTrimmed);
    const endMatch = /^(\d{4})-(\d{2})-(\d{2})$/.exec(endTrimmed);

    if (startMatch === null || endMatch === null) {
        return null;
    }

    const utcStart = Date.UTC(
        Number(startMatch[1]),
        Number(startMatch[2]) - 1,
        Number(startMatch[3]),
    );
    const utcEnd = Date.UTC(
        Number(endMatch[1]),
        Number(endMatch[2]) - 1,
        Number(endMatch[3]),
    );

    if (utcEnd < utcStart) {
        return null;
    }

    return Math.floor((utcEnd - utcStart) / 86400000) + 1;
}
