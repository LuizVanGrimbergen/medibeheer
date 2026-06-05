export function readFamilyScreenQueryParam(
    name: string,
    pageUrl: string,
): string | null {
    const value = new URL(pageUrl, window.location.origin).searchParams.get(
        name,
    );

    if (value === null || !/^\d+$/.test(value)) {
        return null;
    }

    return value;
}

export function readFamilyScreenQueryFlag(
    name: string,
    expectedValue: string,
    pageUrl: string,
): boolean {
    const value = new URL(pageUrl, window.location.origin).searchParams.get(
        name,
    );

    return value === expectedValue;
}

export const familyOverviewUpdatesOpenQuery = {
    name: 'updates',
    value: 'open',
} as const;
