export function formatHistoryCalendarLongDate(
    isoDate: string,
    locale: string,
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

    const localeTag = locale === 'nl' ? 'nl-NL' : undefined;

    return new Intl.DateTimeFormat(localeTag, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(year, month - 1, day));
}

export function todayIsoDateKey(): string {
    const now = new Date();

    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
}
