export const REMAINING_DAYS_PER_MONTH = 31;

export const REMAINING_MONTHS_PER_YEAR = 12;

export const REMAINING_DAYS_PER_YEAR =
    REMAINING_DAYS_PER_MONTH * REMAINING_MONTHS_PER_YEAR;

export type RemainingDaysDurationParts = {
    years: number;
    months: number;
    days: number;
};

export function decomposeRemainingDaysDuration(
    totalDays: number,
): RemainingDaysDurationParts | null {
    if (totalDays < REMAINING_DAYS_PER_MONTH) {
        return null;
    }

    const years = Math.floor(totalDays / REMAINING_DAYS_PER_YEAR);
    const remainderAfterYears = totalDays % REMAINING_DAYS_PER_YEAR;
    const months = Math.floor(remainderAfterYears / REMAINING_DAYS_PER_MONTH);
    const days = remainderAfterYears % REMAINING_DAYS_PER_MONTH;

    return { years, months, days };
}

type RemainingDaysDurationTranslate = (
    key: string,
    values?: Record<string, string>,
) => string;

export type RemainingDaysDurationUnitKeys = {
    year: string;
    monthOne: string;
    months: string;
    dayOne: string;
    days: string;
};

export function buildRemainingDaysDurationSegments(
    t: RemainingDaysDurationTranslate,
    parts: RemainingDaysDurationParts,
    unitKeys: RemainingDaysDurationUnitKeys,
): string[] {
    const segments: string[] = [];

    if (parts.years > 0) {
        segments.push(
            t(unitKeys.year, {
                count: String(parts.years),
            }),
        );
    }

    if (parts.months === 1) {
        segments.push(t(unitKeys.monthOne));
    } else if (parts.months > 1) {
        segments.push(
            t(unitKeys.months, {
                count: String(parts.months),
            }),
        );
    }

    if (parts.days === 1) {
        segments.push(t(unitKeys.dayOne));
    } else if (parts.days > 1) {
        segments.push(
            t(unitKeys.days, {
                count: String(parts.days),
            }),
        );
    }

    return segments;
}

export function joinRemainingDaysDurationSegments(segments: string[]): string {
    const [first, second, ...rest] = segments;

    if (first === undefined) {
        return '';
    }

    if (second === undefined) {
        return first;
    }

    if (rest.length === 0) {
        return `${first} en ${second}`;
    }

    const leading = [first, second, ...rest.slice(0, -1)].join(', ');
    const last = rest.at(-1);

    return last === undefined ? leading : `${leading} en ${last}`;
}

export function formatDecomposedRemainingDaysDurationLine(
    t: RemainingDaysDurationTranslate,
    parts: RemainingDaysDurationParts,
    unitKeys: RemainingDaysDurationUnitKeys,
    statusLineKey: string,
): string {
    const duration = joinRemainingDaysDurationSegments(
        buildRemainingDaysDurationSegments(t, parts, unitKeys),
    );

    return t(statusLineKey, { duration });
}
