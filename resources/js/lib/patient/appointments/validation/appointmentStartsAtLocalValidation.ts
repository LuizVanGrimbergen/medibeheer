const LOCAL_DATE_ISO_RE = /^(\d{4})-(\d{2})-(\d{2})$/;
const LOCAL_TIME_HM_RE = /^(\d{1,2}):(\d{2})$/;

export function localCalendarDateIsoToday(): string {
    const now = new Date();

    const pad2 = (n: number) => String(n).padStart(2, '0');

    return `${now.getFullYear()}-${pad2(now.getMonth() + 1)}-${pad2(now.getDate())}`;
}

export function parseLocalAppointmentDateTime(dateStr: string, timeStr: string): Date | null {
    const dateMatch = LOCAL_DATE_ISO_RE.exec(dateStr.trim());

    if (dateMatch === null) {
        return null;
    }

    const timeMatch = LOCAL_TIME_HM_RE.exec(timeStr.trim());

    if (timeMatch === null) {
        return null;
    }

    const year = Number(dateMatch[1]);
    const month = Number(dateMatch[2]);
    const day = Number(dateMatch[3]);
    const hour = Number(timeMatch[1]);
    const minute = Number(timeMatch[2]);

    if (
        month < 1
        || month > 12
        || day < 1
        || day > 31
        || hour < 0
        || hour > 23
        || minute < 0
        || minute > 59
    ) {
        return null;
    }

    const candidate = new Date(year, month - 1, day, hour, minute, 0);

    if (
        candidate.getFullYear() !== year
        || candidate.getMonth() !== month - 1
        || candidate.getDate() !== day
        || candidate.getHours() !== hour
        || candidate.getMinutes() !== minute
    ) {
        return null;
    }

    return candidate;
}
