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

export function parseMedicationDoseNumericCount(dose: string): number | null {
    const trimmed = dose.trim();

    if (trimmed.length < 1) {
        return null;
    }

    const normalized = trimmed.replace(',', '.');
    const n = Number.parseFloat(normalized);

    if (!Number.isFinite(n)) {
        return null;
    }

    return n;
}

/** Keeps only digits and at most one decimal separator while the user types. */
export function filterDecimalAmountInput(raw: string): string {
    const cleaned = raw.replace(/[^\d.,]/g, '');
    const separatorIndex = cleaned.search(/[.,]/);

    if (separatorIndex === -1) {
        return cleaned;
    }

    const separator = cleaned[separatorIndex] ?? '.';
    const before = cleaned.slice(0, separatorIndex);
    const after = cleaned.slice(separatorIndex + 1).replace(/[.,]/g, '');

    return `${before}${separator}${after}`;
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
