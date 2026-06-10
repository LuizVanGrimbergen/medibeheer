/** Wizard `<form>`: fills remaining dialog height. */
export const mobileShellWizardFormClass =
    'flex min-h-0 min-w-0 flex-1 basis-0 flex-col overflow-hidden';

/** Shell wrapping the scroll column and scroll-hint overlay. */
export const mobileShellWizardShellClass =
    'relative flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden';

/**
 * Scroll column: fields first, footer after. mt-auto on the footer pins short steps to the bottom.
 */
export const mobileShellWizardScrollBodyClass =
    'flex min-h-0 flex-1 flex-col overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch]';

/** Footer inside {@link mobileShellWizardScrollBodyClass}; scroll-hint hides once this is in view. */
export const mobileShellWizardFooterClass =
    'mt-auto shrink-0 pt-4 pb-[max(0.75rem,env(safe-area-inset-bottom,0px))] md:pt-5';

/**
 * Stretch a full-page wizard to the patient layout scroll column (above footer nav).
 * Pair with {@link mobileShellPageRootClass} on the wizard root.
 */
export const mobileShellPageFillClass = 'flex min-h-0 flex-1 flex-col';

/** Full-page wizard routes: same width as {@link mobileShellDialogContentClass} desktop panel. */
export const mobileShellPageRootClass =
    'mx-auto flex w-full min-h-0 flex-1 flex-col gap-4 md:max-w-[min(36rem,calc(100vw-2rem))] md:gap-4 lg:max-w-[min(42rem,calc(100vw-2.5rem))] lg:gap-6';

/** Matches DialogHeader on patient shell dialogs. */
export const mobileShellDialogHeaderClass =
    'shrink-0 space-y-1.5 pt-[env(safe-area-inset-top,0)] text-left sm:space-y-1 sm:pt-0 md:space-y-1';

export const mobileShellDialogTitleClass =
    'text-text-heading text-xl leading-tight font-bold md:text-2xl';

export const mobileShellDialogDescriptionClass =
    'text-text-heading block text-sm leading-snug font-medium md:text-base md:leading-relaxed';

export const mobileShellWizardStepPanelClass = 'space-y-3 sm:space-y-4';

export const mobileShellWizardCardClass =
    'border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] sm:rounded-3xl';

export const mobileShellWizardCardInnerClass =
    'bg-surface space-y-5 rounded-2xl px-4 py-4 sm:space-y-6 sm:rounded-3xl sm:px-5 sm:py-5 md:p-7 lg:p-8';

export const mobileShellWizardFooterCardClass =
    'border-border/80 text-text rounded-2xl border bg-transparent shadow-sm shadow-black/[0.03] sm:rounded-3xl';

export const mobileShellWizardFooterCardInnerClass = 'px-4 py-3 sm:px-5 sm:py-4 md:px-7 lg:px-8';

export const mobileShellFormWizardFooterRowClass =
    'flex w-full min-w-0 flex-col-reverse gap-2 sm:flex-row sm:gap-3';

export const mobileShellFormWizardFooterPrimaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

/** Primary CTA in inline action rows (family plans, review page). */
export const mobileShellActionPrimaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold sm:w-auto sm:flex-1 md:min-h-14 md:px-4 md:text-lg';

/** Outline action in inline rows (review, secondary family actions). */
export const mobileShellActionOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl px-3 text-base font-semibold sm:w-auto sm:flex-1 md:min-h-14 md:px-4 md:text-lg';

/** Neutral outline action in inline rows (decline, cancel). */
export const mobileShellActionSecondaryOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-border px-3 text-base font-semibold sm:w-auto sm:flex-1 md:min-h-14 md:px-4 md:text-lg';

/** Destructive outline action in inline rows. */
export const mobileShellActionDangerOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-danger/40 bg-danger/10 px-3 text-base font-semibold text-danger hover:border-danger hover:bg-danger/20 hover:text-danger sm:w-auto sm:flex-1 md:min-h-14 md:px-4 lg:text-lg';

export const mobileShellFormWizardFooterCancelButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-danger/40 bg-danger/10 px-3 text-base font-semibold text-danger hover:border-danger hover:bg-danger/20 hover:text-danger md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

/** Secondary action in patient confirm dialogs (e.g. Annuleren). */
export const mobileShellFormWizardFooterOutlineButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-border bg-surface px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const mobileShellDialogMobileClassMaxSm = [
    'min-h-0 flex-col text-text',
    'max-sm:fixed max-sm:inset-0 max-sm:!z-[110] max-sm:!left-0 max-sm:!top-0',
    'max-sm:h-[100dvh] max-sm:!w-screen max-sm:!max-w-none max-sm:!translate-x-0 max-sm:!translate-y-0',
    'max-sm:overflow-hidden max-sm:overscroll-contain max-sm:rounded-none max-sm:border-0 max-sm:p-4',
    'max-sm:shadow-lg max-sm:touch-manipulation',
].join(' ');

const mobileShellDialogMobileClassMaxMd = [
    'min-h-0 flex-col text-text',
    'max-md:fixed max-md:inset-0 max-md:!z-[110] max-md:!left-0 max-md:!top-0',
    'max-md:h-[100dvh] max-md:!w-screen max-md:!max-w-none max-md:!translate-x-0 max-md:!translate-y-0',
    'max-md:overflow-hidden max-md:overscroll-contain max-md:rounded-none max-md:border-0 max-md:p-4',
    'max-md:shadow-lg max-md:touch-manipulation',
].join(' ');

const mobileShellDialogDesktopFromSm = [
    'sm:h-auto sm:!top-[4.75rem] sm:!bottom-0 sm:!translate-y-0 sm:!max-h-none sm:w-[min(36rem,calc(100vw-2rem))] sm:max-w-none sm:overflow-hidden',
    'sm:gap-4 sm:rounded-2xl sm:border-2 sm:border-border sm:p-5 sm:shadow-lg',
    'lg:gap-6 lg:p-6',
].join(' ');

const mobileShellDialogDesktopFromMd = [
    'md:h-auto md:!top-[4.75rem] md:!bottom-0 md:!translate-y-0 md:!max-h-none md:w-[min(36rem,calc(100vw-2rem))] md:max-w-none md:overflow-hidden',
    'md:gap-4 md:rounded-2xl md:border-2 md:border-border md:p-5 md:shadow-lg',
    'lg:w-[min(42rem,calc(100vw-2.5rem))] lg:gap-6 lg:p-6',
].join(' ');

/**
 * Reka-ui DialogContent classes: edge-to-edge below the desktop breakpoint, inset below AppNavbar from sm or md up (footer nav is hidden while open).
 *
 * Desktop `top` is at least ~4.75rem so the panel clears the sticky AppNavbar (py-4 + content + border).
 *
 * @param desktopFrom use `sm` for appointment flows (first centered layout at 640px; fullscreen below sm), `md` for medication (768px; fullscreen below md).
 */
export function mobileShellDialogContentClass(
    desktopFrom: 'sm' | 'md',
): string {
    const mobile =
        desktopFrom === 'md'
            ? mobileShellDialogMobileClassMaxMd
            : mobileShellDialogMobileClassMaxSm;
    const desktop =
        desktopFrom === 'md'
            ? mobileShellDialogDesktopFromMd
            : mobileShellDialogDesktopFromSm;

    return `${mobile} [&>form]:min-h-0 [&>form]:flex-1 [&>form]:basis-0 ${desktop}`;
}

/** DialogOverlay: sit above app chrome (z-100) while the shell is fullscreen. Must match `mobileShellDialogContentClass` breakpoint. */
export function mobileShellDialogOverlayAboveAppChromeClass(
    desktopFrom: 'sm' | 'md',
): string {
    return desktopFrom === 'sm' ? 'max-sm:!z-[108]' : 'max-md:!z-[108]';
}

/** Default shell layout for {@link PatientConfirmDialog} (matches prescription/medication dialogs). */
export const mobileShellConfirmDialogContentClass =
    mobileShellDialogContentClass('md');

/** Centered copy block inside {@link PatientConfirmDialog} (matches success screens). */
export const mobileShellConfirmDialogMessageClass =
    'flex min-h-0 flex-1 flex-col items-center justify-center px-6 py-10 text-center sm:px-10';

/** Optional hero icon above confirm dialog copy (matches {@link ActionSuccessScreen}). */
export const mobileShellConfirmDialogIconWrapClass =
    'mb-8 flex size-20 items-center justify-center rounded-2xl border-2 sm:size-24';

export const mobileShellConfirmDialogIconClass = 'size-12 shrink-0 sm:size-14';

export const mobileShellConfirmDialogIconWrapDangerClass =
    'border-danger/40 bg-danger/10 text-danger';

export const mobileShellConfirmDialogIconWrapPrimaryClass =
    'border-primary/40 bg-primary/10 text-primary';

/** Centers a short wizard step in the scroll column (footer stays pinned via mt-auto). */
export const mobileShellWizardCenteredStepClass =
    'flex min-h-0 w-full flex-1 flex-col items-center justify-center px-4 py-8 sm:px-6 sm:py-10';
