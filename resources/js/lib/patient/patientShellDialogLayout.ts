const patientShellDialogMobileClassMaxSm = [
    'flex min-h-0 flex-col text-text',
    'max-sm:fixed max-sm:inset-0 max-sm:!z-[110] max-sm:!left-0 max-sm:!top-0',
    'max-sm:h-[100dvh] max-sm:!w-screen max-sm:!max-w-none max-sm:!translate-x-0 max-sm:!translate-y-0',
    'max-sm:overflow-hidden max-sm:overscroll-contain max-sm:rounded-none max-sm:border-0 max-sm:p-4',
    'max-sm:shadow-lg max-sm:touch-manipulation',
].join(' ');

const patientShellDialogMobileClassMaxMd = [
    'flex min-h-0 flex-col text-text',
    'max-md:fixed max-md:inset-0 max-md:!z-[110] max-md:!left-0 max-md:!top-0',
    'max-md:h-[100dvh] max-md:!w-screen max-md:!max-w-none max-md:!translate-x-0 max-md:!translate-y-0',
    'max-md:overflow-hidden max-md:overscroll-contain max-md:rounded-none max-md:border-0 max-md:p-4',
    'max-md:shadow-lg max-md:touch-manipulation',
].join(' ');

const patientShellDialogDesktopFromSm = [
    'sm:h-auto sm:!top-[4.75rem] sm:!bottom-[5.5rem] sm:!translate-y-0 sm:!max-h-none sm:w-[min(36rem,calc(100vw-2rem))] sm:max-w-none sm:overflow-hidden',
    'sm:gap-4 sm:rounded-2xl sm:border-2 sm:border-border sm:p-5 sm:shadow-lg',
    'lg:gap-6 lg:p-6',
].join(' ');

const patientShellDialogDesktopFromMd = [
    'md:h-auto md:!top-[4.75rem] md:!bottom-[5.5rem] md:!translate-y-0 md:!max-h-none md:w-[min(36rem,calc(100vw-2rem))] md:max-w-none md:overflow-hidden',
    'md:gap-4 md:rounded-2xl md:border-2 md:border-border md:p-5 md:shadow-lg',
    'lg:w-[min(42rem,calc(100vw-2.5rem))] lg:gap-6 lg:p-6',
].join(' ');

/**
 * Reka-ui DialogContent classes: edge-to-edge below the desktop breakpoint, inset below AppNavbar / above bottom nav from sm or md up.
 *
 * Desktop `top` is at least ~4.75rem so the panel clears the sticky AppNavbar (py-4 + content + border).
 *
 * @param desktopFrom use `sm` for appointment flows (first centered layout at 640px; fullscreen below sm), `md` for medication (768px; fullscreen below md).
 */
export function patientShellDialogContentClass(
    desktopFrom: 'sm' | 'md',
): string {
    const mobile =
        desktopFrom === 'md'
            ? patientShellDialogMobileClassMaxMd
            : patientShellDialogMobileClassMaxSm;
    const desktop =
        desktopFrom === 'md'
            ? patientShellDialogDesktopFromMd
            : patientShellDialogDesktopFromSm;

    return `${mobile} ${desktop}`;
}

/** DialogOverlay: sit above app chrome (z-100) while the shell is fullscreen. Must match `patientShellDialogContentClass` breakpoint. */
export function patientShellDialogOverlayAboveAppChromeClass(
    desktopFrom: 'sm' | 'md',
): string {
    return desktopFrom === 'sm' ? 'max-sm:!z-[108]' : 'max-md:!z-[108]';
}
