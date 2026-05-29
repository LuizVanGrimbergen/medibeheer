export function formatCareTeamExpiry(iso: string): string {
    const date = new Date(iso);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return new Intl.DateTimeFormat('nl-BE', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(date);
}
