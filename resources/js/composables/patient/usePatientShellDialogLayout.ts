import {
    patientShellDialogContentClass,
    patientShellDialogOverlayAboveAppChromeClass,
} from '@/lib/patient/patientShellDialogLayout';

export function usePatientShellDialogLayout(
    desktopFrom: 'sm' | 'md' = 'md',
): {
    dialogContentClass: string;
    overlayClass: string;
} {
    return {
        dialogContentClass: patientShellDialogContentClass(desktopFrom),
        overlayClass: patientShellDialogOverlayAboveAppChromeClass(desktopFrom),
    };
}
