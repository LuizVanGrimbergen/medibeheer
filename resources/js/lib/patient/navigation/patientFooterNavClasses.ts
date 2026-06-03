import type { PatientNavigationAlertTone } from '@/lib/types';

export type PatientFooterNavRouteName =
    | 'patient.dashboard'
    | 'patient.medications'
    | 'patient.prescriptions'
    | 'patient.inventory'
    | 'patient.appointments'
    | 'patient.family';

export type PatientNavigationSharedProps = {
    inventory: PatientNavigationAlertTone | null;
    prescriptions: PatientNavigationAlertTone | null;
};

export function patientFooterNavAlertTone(
    routeName: PatientFooterNavRouteName,
    navigation: PatientNavigationSharedProps | undefined,
): PatientNavigationAlertTone | null {
    if (navigation === undefined) {
        return null;
    }

    if (routeName === 'patient.inventory') {
        return navigation.inventory;
    }

    if (routeName === 'patient.prescriptions') {
        return navigation.prescriptions;
    }

    return null;
}

export function patientFooterNavAlertAccentClass(
    tone: PatientNavigationAlertTone,
): string {
    if (tone === 'critical') {
        return 'text-danger';
    }

    return 'text-stock-near dark:text-stock-near-dark';
}
