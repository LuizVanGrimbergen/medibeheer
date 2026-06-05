import type { PatientNavigationSharedProps } from '@/lib/patient/navigation/patientFooterNavClasses';
import {
    moreUrgentNavAlert,
    worstInventoryNavAlertFromMedications,
    worstPrescriptionNavAlertFromPrescriptions,
} from '@/lib/patient/navigation/patientNavigationAlertTone';
import type {
    MedicationListItem,
    MedicationPrescriptionListItem,
    PageProps,
    Paginated,
} from '@/lib/types';

function isPaginated<T>(value: unknown): value is Paginated<T> {
    if (typeof value !== 'object' || value === null) {
        return false;
    }

    return Array.isArray((value as Paginated<T>).data);
}

function medicationListsFromPageProps(
    pageProps: PageProps,
): MedicationListItem[][] {
    const lists: MedicationListItem[][] = [];
    const record = pageProps as PageProps & Record<string, unknown>;

    if (isPaginated<MedicationListItem>(record.medications)) {
        lists.push(record.medications.data);
    }

    if (isPaginated<MedicationListItem>(record.active_medications)) {
        lists.push(record.active_medications.data);
    }

    return lists;
}

function prescriptionsFromPageProps(
    pageProps: PageProps,
): MedicationPrescriptionListItem[] {
    const record = pageProps as PageProps & Record<string, unknown>;

    if (!isPaginated<MedicationPrescriptionListItem>(record.prescriptions)) {
        return [];
    }

    return record.prescriptions.data;
}

export function resolvePatientNavigationAlerts(
    pageProps: PageProps,
): PatientNavigationSharedProps {
    const server = pageProps.patient_navigation ?? {
        inventory: null,
        prescriptions: null,
    };

    let inventory = server.inventory;
    let prescriptions = server.prescriptions;

    for (const medications of medicationListsFromPageProps(pageProps)) {
        inventory = moreUrgentNavAlert(
            inventory,
            worstInventoryNavAlertFromMedications(medications),
        );
    }

    const pagePrescriptions = prescriptionsFromPageProps(pageProps);

    if (pagePrescriptions.length > 0) {
        prescriptions = moreUrgentNavAlert(
            prescriptions,
            worstPrescriptionNavAlertFromPrescriptions(pagePrescriptions),
        );
    }

    return {
        inventory,
        prescriptions,
    };
}
