export function formatInventoryVacationDateLabel(iso: string): string {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso.trim());

    if (match === null) {
        return iso;
    }

    const date = new Date(
        Number(match[1]),
        Number(match[2]) - 1,
        Number(match[3]),
    );

    return new Intl.DateTimeFormat('nl-NL', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
}
