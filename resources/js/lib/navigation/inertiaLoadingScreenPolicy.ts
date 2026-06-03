export type InertiaVisitOptions = {
    url: string | URL;
    method: string;
    only?: string[];
};

function visitUrlPath(url: string | URL): string {
    if (url instanceof URL) {
        return url.pathname;
    }

    return url.split('?')[0] ?? url;
}

function visitMethod(method: string): string {
    return method.toLowerCase();
}

export type LoadingScreenMessageKey =
    | 'default'
    | 'savingMedication'
    | 'savingIntake'
    | 'savingPrescription'
    | 'savingAppointment'
    | 'savingCheckin';

const LOADING_SCREEN_SHOW_DELAY_MS = 200;
const LOADING_SCREEN_MIN_VISIBLE_MS = 400;

export function loadingScreenShowDelayMs(): number {
    return LOADING_SCREEN_SHOW_DELAY_MS;
}

export function loadingScreenMinVisibleMs(): number {
    return LOADING_SCREEN_MIN_VISIBLE_MS;
}

export function shouldShowLoadingScreenForVisit(
    visit: InertiaVisitOptions,
): boolean {
    const { only } = visit;

    if (Array.isArray(only) && only.length > 0) {
        return false;
    }

    return true;
}

export function resolveLoadingScreenMessageKey(
    visit: InertiaVisitOptions,
): LoadingScreenMessageKey {
    const url = visitUrlPath(visit.url).toLowerCase();
    const method = visitMethod(visit.method);

    if (method !== 'post' && method !== 'put' && method !== 'patch') {
        return 'default';
    }

    if (url.includes('medication-intakes')) {
        return 'savingIntake';
    }

    if (url.includes('medications')) {
        return 'savingMedication';
    }

    if (url.includes('prescriptions')) {
        return 'savingPrescription';
    }

    if (url.includes('appointments')) {
        return 'savingAppointment';
    }

    if (url.includes('daily-checkins')) {
        return 'savingCheckin';
    }

    return 'default';
}
