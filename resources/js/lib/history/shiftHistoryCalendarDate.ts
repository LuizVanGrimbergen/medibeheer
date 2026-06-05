export function historyCalendarMonthKey(isoDate: string): string {
    return isoDate.slice(0, 7);
}

export function shiftHistoryCalendarDate(
    isoDate: string,
    deltaDays: number,
): string {
    const parts = isoDate.split('-').map(Number);
    const year = parts[0];
    const month = parts[1];
    const day = parts[2];

    if (
        year === undefined ||
        month === undefined ||
        day === undefined ||
        Number.isNaN(year) ||
        Number.isNaN(month) ||
        Number.isNaN(day)
    ) {
        return isoDate;
    }

    const date = new Date(year, month - 1, day);
    date.setDate(date.getDate() + deltaDays);

    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}
