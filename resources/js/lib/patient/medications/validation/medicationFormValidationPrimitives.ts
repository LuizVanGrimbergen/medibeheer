export function isMemberOf<const T extends readonly string[]>(
    allowed: T,
    value: string,
): value is T[number] {
    return (allowed as readonly string[]).includes(value);
}

export function trimmedRequiredMaxError(
    raw: string,
    maxLen: number,
    messages: { required: string; max: string },
): string | undefined {
    const t = raw.trim();

    if (t.length < 1) {
        return messages.required;
    }

    if (t.length > maxLen) {
        return messages.max;
    }

    return undefined;
}

export function parseMedicationTimesPerDayCount(value: string): number | null {
    const trimmed = value.trim();

    if (!/^\d+$/.test(trimmed)) {
        return null;
    }

    const count = Number(trimmed);

    if (!Number.isInteger(count) || count < 1 || count > 24) {
        return null;
    }

    return count;
}
