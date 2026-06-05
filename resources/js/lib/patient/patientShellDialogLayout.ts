/** Wizard `<form>`: fills remaining dialog height. */
export const patientShellWizardFormClass =
    'flex min-h-0 min-w-0 flex-1 basis-0 flex-col overflow-hidden';

/** Shell wrapping the scroll column and scroll-hint overlay. */
export const patientShellWizardShellClass =
    'relative flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden';

/**
 * Scroll column: fields first, footer after. mt-auto on the footer pins short steps to the bottom.
 */
export const patientShellWizardScrollBodyClass =
    'flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch]';

/** Footer inside {@link patientShellWizardScrollBodyClass}; scroll-hint hides once this is in view. */
export const patientShellWizardFooterClass =
    'mt-auto shrink-0 pt-4 pb-[max(0.75rem,env(safe-area-inset-bottom,0px))] md:pt-5';

/**
 * Stretch a full-page wizard to the patient layout scroll column (above footer nav).
 * Pair with {@link patientShellPageRootClass} on the wizard root.
 */
export const patientShellPageFillClass = 'flex min-h-0 flex-1 flex-col';

/** Full-page wizard routes: same width as {@link patientShellDialogContentClass} desktop panel. */
export const patientShellPageRootClass =
    'mx-auto flex w-full min-h-0 flex-1 flex-col gap-4 md:max-w-[min(36rem,calc(100vw-2rem))] md:gap-4 lg:max-w-[min(42rem,calc(100vw-2.5rem))] lg:gap-6';

/** Matches DialogHeader on patient shell dialogs. */
export const patientShellPageHeaderClass =
    'shrink-0 space-y-1.5 pt-[env(safe-area-inset-top,0)] text-left sm:space-y-1 sm:pt-0 md:space-y-1';

export const patientShellPageTitleClass =
    'text-text-heading text-xl leading-tight font-bold md:text-2xl';

export const patientShellPageDescriptionClass =
    'text-text-heading block text-sm leading-snug font-medium md:text-base md:leading-relaxed';

export const patientShellWizardStepPanelClass = 'space-y-3 md:space-y-3';

export const patientShellWizardCardClass =
    'border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] md:rounded-3xl';

export const patientShellWizardCardInnerClass =
    'bg-surface space-y-5 rounded-2xl px-4 py-4 md:space-y-5 md:rounded-3xl md:px-5 md:py-5 lg:space-y-6 lg:px-7 lg:py-7';

export const patientFormWizardFooterRowClass =
    'flex w-full min-w-0 flex-col gap-2 md:flex-row-reverse md:gap-3';

export const patientFormWizardFooterPrimaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

export const patientFormWizardFooterCancelButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-danger/40 bg-danger/10 px-3 text-base font-semibold text-danger hover:border-danger hover:bg-danger/20 hover:text-danger md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

/** Secondary action in patient confirm dialogs (e.g. Annuleren). */
export const patientFormWizardFooterOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-border bg-surface px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const patientShellDialogMobileClassMaxSm = [
    'min-h-0 flex-col text-text',
    'max-sm:fixed max-sm:inset-0 max-sm:!z-[110] max-sm:!left-0 max-sm:!top-0',
    'max-sm:h-[100dvh] max-sm:!w-screen max-sm:!max-w-none max-sm:!translate-x-0 max-sm:!translate-y-0',
    'max-sm:overflow-hidden max-sm:overscroll-contain max-sm:rounded-none max-sm:border-0 max-sm:p-4',
    'max-sm:shadow-lg max-sm:touch-manipulation',
].join(' ');

const patientShellDialogMobileClassMaxMd = [
    'min-h-0 flex-col text-text',
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

    return `${mobile} [&>form]:min-h-0 [&>form]:flex-1 [&>form]:basis-0 ${desktop}`;
}

/** DialogOverlay: sit above app chrome (z-100) while the shell is fullscreen. Must match `patientShellDialogContentClass` breakpoint. */
export function patientShellDialogOverlayAboveAppChromeClass(
    desktopFrom: 'sm' | 'md',
): string {
    return desktopFrom === 'sm' ? 'max-sm:!z-[108]' : 'max-md:!z-[108]';
}

/** Default shell layout for {@link PatientConfirmDialog} (matches prescription/medication dialogs). */
export const patientConfirmDialogContentClass =
    patientShellDialogContentClass('md');

/** Centered copy block inside {@link PatientConfirmDialog} (matches success screens). */
export const patientConfirmDialogMessageClass =
    'flex min-h-0 flex-1 flex-col items-center justify-center px-6 py-10 text-center sm:px-10';

/** Optional hero icon above confirm dialog copy (matches {@link ActionSuccessScreen}). */
export const patientConfirmDialogIconWrapClass =
    'mb-8 flex size-20 items-center justify-center rounded-2xl border-2 sm:size-24';

export const patientConfirmDialogIconClass = 'size-12 shrink-0 sm:size-14';

export const patientConfirmDialogIconWrapDangerClass =
    'border-danger/40 bg-danger/10 text-danger';

export const patientConfirmDialogIconWrapPrimaryClass =
    'border-primary/40 bg-primary/10 text-primary';

/** Centers a short wizard step in the scroll column (footer stays pinned via mt-auto). */
export const patientShellWizardCenteredStepClass =
    'flex min-h-0 w-full flex-1 flex-col items-center justify-center px-4 py-8 sm:px-6 sm:py-10';
