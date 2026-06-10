import {
    mobileShellDialogContentClass,
    mobileShellDialogOverlayAboveAppChromeClass,
} from '@/lib/shell/mobileShellDialogLayout';

export function useMobileShellDialogLayout(desktopFrom: 'sm' | 'md' = 'md'): {
    dialogContentClass: string;
    overlayClass: string;
} {
    return {
        dialogContentClass: mobileShellDialogContentClass(desktopFrom),
        overlayClass: mobileShellDialogOverlayAboveAppChromeClass(desktopFrom),
    };
}
