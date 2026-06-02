import {
    MEDICATION_URGENCY_CRITICAL_MAX_DAYS,
    MEDICATION_URGENCY_PROGRESS_FULL_DAYS,
    MEDICATION_URGENCY_WARNING_MAX_DAYS,
} from './medicationUrgencyThresholds';

export type MedicationUrgencyTone = 'critical' | 'warning' | 'safe';

export function medicationUrgencyToneFromDaysRemaining(
    daysRemaining: number,
): MedicationUrgencyTone {
    if (daysRemaining <= MEDICATION_URGENCY_CRITICAL_MAX_DAYS) {
        return 'critical';
    }

    if (daysRemaining <= MEDICATION_URGENCY_WARNING_MAX_DAYS) {
        return 'warning';
    }

    return 'safe';
}

export function medicationUrgencyProgressPercent(
    daysRemaining: number | null,
): number | null {
    if (daysRemaining === null) {
        return null;
    }

    const days = Math.max(0, daysRemaining);
    const capped = Math.min(days, MEDICATION_URGENCY_PROGRESS_FULL_DAYS);

    return Math.round((capped / MEDICATION_URGENCY_PROGRESS_FULL_DAYS) * 100);
}

export function medicationUrgencyShowsAlertRow(
    tone: MedicationUrgencyTone | null,
): boolean {
    return tone === 'critical' || tone === 'warning';
}

export function medicationUrgencyProgressIndicatorClass(
    tone: MedicationUrgencyTone | null,
): string | undefined {
    if (tone === 'critical') {
        return 'bg-danger';
    }

    if (tone === 'warning') {
        return 'bg-stock-near dark:bg-stock-near-dark';
    }

    if (tone === 'safe') {
        return 'bg-success';
    }

    return undefined;
}

export function medicationUrgencyStatusTextClass(
    tone: MedicationUrgencyTone | null,
): string {
    if (tone === 'critical') {
        return 'text-danger';
    }

    if (tone === 'warning') {
        return 'text-stock-near dark:text-stock-near-dark';
    }

    if (tone === 'safe') {
        return 'text-success';
    }

    return 'text-text-heading';
}
