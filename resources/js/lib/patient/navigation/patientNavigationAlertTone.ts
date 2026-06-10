import { medicationListVisualTone } from '@/lib/patient/inventory/medicationListVisualTone';
import type { MedicationUrgencyTone } from '@/lib/patient/medications/urgency/medicationUrgencyTone';
import { prescriptionExpiryUrgencyContext } from '@/lib/patient/prescriptions/prescriptionExpiryUrgency';
import type {
    MedicationRegisterItem,
    MedicationPrescriptionItem,
    PatientNavigationAlertTone,
} from '@/lib/types';

const NAV_ALERT_RANK: Record<PatientNavigationAlertTone, number> = {
    critical: 0,
    warning: 1,
};

const URGENCY_TONE_RANK: Record<MedicationUrgencyTone, number> = {
    critical: 0,
    warning: 1,
    safe: 2,
};

export function navAlertToneFromUrgency(
    tone: MedicationUrgencyTone | null,
): PatientNavigationAlertTone | null {
    if (tone === 'critical' || tone === 'warning') {
        return tone;
    }

    return null;
}

export function moreUrgentNavAlert(
    left: PatientNavigationAlertTone | null,
    right: PatientNavigationAlertTone | null,
): PatientNavigationAlertTone | null {
    if (left === null) {
        return right;
    }

    if (right === null) {
        return left;
    }

    return NAV_ALERT_RANK[left] <= NAV_ALERT_RANK[right] ? left : right;
}

function moreUrgentUrgencyTone(
    left: MedicationUrgencyTone | null,
    right: MedicationUrgencyTone,
): MedicationUrgencyTone {
    if (left === null) {
        return right;
    }

    return URGENCY_TONE_RANK[right] < URGENCY_TONE_RANK[left] ? right : left;
}

export function worstInventoryNavAlertFromMedications(
    medications: readonly MedicationRegisterItem[],
): PatientNavigationAlertTone | null {
    let worst: MedicationUrgencyTone | null = null;

    for (const medication of medications) {
        const tone = medicationListVisualTone(medication);

        if (tone === null) {
            continue;
        }

        worst = moreUrgentUrgencyTone(worst, tone);
    }

    return navAlertToneFromUrgency(worst);
}

export function worstPrescriptionNavAlertFromPrescriptions(
    prescriptions: readonly MedicationPrescriptionItem[],
): PatientNavigationAlertTone | null {
    let worst: MedicationUrgencyTone | null = null;

    for (const prescription of prescriptions) {
        const context = prescriptionExpiryUrgencyContext(
            prescription.prescription_expiry_date,
        );

        if (context === null) {
            continue;
        }

        worst = moreUrgentUrgencyTone(worst, context.tone);
    }

    return navAlertToneFromUrgency(worst);
}
