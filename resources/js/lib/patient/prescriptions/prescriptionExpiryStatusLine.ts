type PrescriptionExpiryTranslate = (
    key: string,
    values?: Record<string, string>,
) => string;

export function prescriptionExpiryStatusLine(
    t: PrescriptionExpiryTranslate,
    daysRemaining: number,
): string {
    if (daysRemaining < 0) {
        return t('patient.prescriptions.expiryStatusExpired');
    }

    if (daysRemaining < 1) {
        return t('patient.prescriptions.expiryStatusExpiresToday');
    }

    if (daysRemaining === 1) {
        return t('patient.prescriptions.expiryStatusOneDay');
    }

    return t('patient.prescriptions.expiryStatusDays', {
        days: String(daysRemaining),
    });
}
