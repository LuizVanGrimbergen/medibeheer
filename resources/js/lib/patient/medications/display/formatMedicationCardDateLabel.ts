export function formatMedicationCardDateLabel(
    ymd: string,
    locale: string,
): string {
    const parts = ymd.split('-').map(Number);
    const y = parts[0];
    const m = parts[1];
    const d = parts[2];

    if (
        y === undefined ||
        m === undefined ||
        d === undefined ||
        Number.isNaN(y) ||
        Number.isNaN(m) ||
        Number.isNaN(d)
    ) {
        return ymd;
    }

    const date = new Date(y, m - 1, d);
    const localeTag = locale === 'nl' ? 'nl-NL' : undefined;

    return new Intl.DateTimeFormat(localeTag, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(date);
}
