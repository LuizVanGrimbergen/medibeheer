export function utcIsoToLocalDatetimeCompositeForForm(isoInstant: string): string {
    const parsed = new Date(isoInstant);

    if (Number.isNaN(parsed.getTime())) {
        return '';
    }

    const pad = (n: number) => String(n).padStart(2, '0');

    return `${parsed.getFullYear()}-${pad(parsed.getMonth() + 1)}-${pad(parsed.getDate())}T${pad(parsed.getHours())}:${pad(parsed.getMinutes())}`;
}
