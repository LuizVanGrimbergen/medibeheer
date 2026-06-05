import { formatRemainingDaysStatusLine } from '@/lib/patient/medications/urgency/formatRemainingDaysStatusLine';

type PrescriptionExpiryTranslate = (
    key: string,
    values?: Record<string, string>,
) => string;

const prescriptionExpiryStatusLineKeys = {
    expired: 'patient.prescriptions.expiryStatusExpired',
    expiresToday: 'patient.prescriptions.expiryStatusExpiresToday',
    oneDay: 'patient.prescriptions.expiryStatusOneDay',
    days: 'patient.prescriptions.expiryStatusDays',
    durationUnits: {
        year: 'patient.duration.year',
        monthOne: 'patient.duration.monthOne',
        months: 'patient.duration.months',
        dayOne: 'patient.duration.dayOne',
        days: 'patient.duration.days',
    },
    durationStatusLine: 'patient.duration.expiryStatusLine',
} as const;

export function prescriptionExpiryStatusLine(
    t: PrescriptionExpiryTranslate,
    daysRemaining: number,
): string {
    return formatRemainingDaysStatusLine(
        t,
        daysRemaining,
        prescriptionExpiryStatusLineKeys,
        'expiry',
    );
}
