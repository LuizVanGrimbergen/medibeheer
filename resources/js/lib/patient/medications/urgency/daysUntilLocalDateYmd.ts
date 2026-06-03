/** Whole calendar days from today (local) until the given YYYY-MM-DD date (negative if past). */
export function daysUntilLocalDateYmd(ymd: string): number | null {
    const trimmed = ymd.trim();
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(trimmed);

    if (match === null) {
        return null;
    }

    const year = Number(match[1]);
    const month = Number(match[2]);
    const day = Number(match[3]);
    const target = new Date(year, month - 1, day);

    if (
        target.getFullYear() !== year ||
        target.getMonth() !== month - 1 ||
        target.getDate() !== day
    ) {
        return null;
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    target.setHours(0, 0, 0, 0);

    const diffMs = target.getTime() - today.getTime();

    return Math.round(diffMs / (24 * 60 * 60 * 1000));
}
