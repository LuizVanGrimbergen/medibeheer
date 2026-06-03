import { daysUntilLocalDateYmd } from '@/lib/patient/medications/urgency/daysUntilLocalDateYmd';
import type { MedicationUrgencyTone } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import {
    medicationUrgencyProgressPercent,
    medicationUrgencyToneFromDaysRemaining,
} from '@/lib/patient/medications/urgency/medicationUrgencyTone';

export type PrescriptionExpiryUrgencyContext = {
    days_remaining: number;
    tone: MedicationUrgencyTone;
    progress_percent: number;
};

export function prescriptionExpiryUrgencyContext(
    prescriptionExpiryDate: string | null,
): PrescriptionExpiryUrgencyContext | null {
    const trimmed = prescriptionExpiryDate?.trim() ?? '';

    if (trimmed.length < 1) {
        return null;
    }

    const daysRemaining = daysUntilLocalDateYmd(trimmed);

    if (daysRemaining === null) {
        return null;
    }

    return {
        days_remaining: daysRemaining,
        tone: medicationUrgencyToneFromDaysRemaining(daysRemaining),
        progress_percent: medicationUrgencyProgressPercent(daysRemaining) ?? 0,
    };
}
