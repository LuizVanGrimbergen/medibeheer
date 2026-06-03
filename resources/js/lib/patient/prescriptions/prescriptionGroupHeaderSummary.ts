import { prescriptionExpiryStatusLine } from '@/lib/patient/prescriptions/prescriptionExpiryStatusLine';
import { prescriptionExpiryUrgencyContext } from '@/lib/patient/prescriptions/prescriptionExpiryUrgency';
import { prescriptionGroupMostUrgentIndex } from '@/lib/patient/prescriptions/prescriptionGroupMostUrgentIndex';
import type { MedicationPrescriptionGroupPrescriptionItem } from '@/lib/types';

type PrescriptionExpiryTranslate = (
    key: string,
    values?: Record<string, string>,
) => string;

export function prescriptionGroupHeaderSummary(
    t: PrescriptionExpiryTranslate,
    prescriptions: MedicationPrescriptionGroupPrescriptionItem[],
): string {
    const mostUrgentIndex = prescriptionGroupMostUrgentIndex(prescriptions);

    if (mostUrgentIndex === null) {
        return t('patient.prescriptions.prescriptionExpiryMissing');
    }

    const context = prescriptionExpiryUrgencyContext(
        prescriptions[mostUrgentIndex]?.prescription_expiry_date ?? null,
    );

    if (context === null) {
        return t('patient.prescriptions.prescriptionExpiryMissing');
    }

    const statusLine = prescriptionExpiryStatusLine(t, context.days_remaining);

    if (prescriptions.length < 2) {
        return statusLine;
    }

    return t('patient.prescriptions.prescriptionCollapsedSummaryNumbered', {
        number: String(mostUrgentIndex + 1),
        status: statusLine,
    });
}
