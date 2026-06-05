export function formatDoctorPatientShortDate(
    isoDate: string,
    locale: string,
): string {
    const [year, month, day] = isoDate.split('-').map(Number);

    if (!year || !month || !day) {
        return isoDate;
    }

    const formatted = new Intl.DateTimeFormat(
        locale === 'nl' ? 'nl-NL' : undefined,
        {
            day: 'numeric',
            month: 'long',
        },
    ).format(new Date(year, month - 1, day));

    return formatted.charAt(0).toUpperCase() + formatted.slice(1);
}
