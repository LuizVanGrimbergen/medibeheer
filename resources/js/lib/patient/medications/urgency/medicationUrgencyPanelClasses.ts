import type { MedicationUrgencyTone } from './medicationUrgencyTone';

const panelBase =
    'flex min-w-0 w-full items-start gap-3.5 rounded-2xl border px-4 py-3.5 sm:gap-4 sm:rounded-3xl sm:px-5 sm:py-4';

const iconWrapBase =
    'flex size-11 shrink-0 items-center justify-center rounded-xl sm:size-14 sm:rounded-2xl';

export function medicationUrgencyPanelClass(
    tone: MedicationUrgencyTone | null,
): string {
    if (tone === 'critical') {
        return `${panelBase} border-danger/25 bg-danger/[0.07] dark:border-danger/35 dark:bg-danger/[0.1]`;
    }

    if (tone === 'warning') {
        return `${panelBase} border-stock-near/25 bg-stock-near/[0.07] dark:border-stock-near-dark/35 dark:bg-stock-near-dark/[0.1]`;
    }

    if (tone === 'safe') {
        return `${panelBase} border-success/25 bg-success/[0.06] dark:border-success/35 dark:bg-success/[0.09]`;
    }

    return `${panelBase} border-border/60 bg-surface-2/90 dark:border-border/70 dark:bg-surface-2`;
}

export function medicationUrgencyPanelIconWrapClass(
    tone: MedicationUrgencyTone | null,
): string {
    if (tone === 'critical') {
        return `${iconWrapBase} bg-danger/15 text-danger dark:bg-danger/20`;
    }

    if (tone === 'warning') {
        return `${iconWrapBase} bg-stock-near/15 text-stock-near dark:bg-stock-near-dark/20 dark:text-stock-near-dark`;
    }

    if (tone === 'safe') {
        return `${iconWrapBase} bg-success/15 text-success dark:bg-success/20`;
    }

    return `${iconWrapBase} bg-primary/12 text-primary`;
}

export function medicationUrgencyOutlineButtonClass(
    tone: MedicationUrgencyTone | null,
): string {
    const base =
        'rounded-3xl border-2 bg-surface text-text-heading hover:bg-surface-hover';

    if (tone === 'critical') {
        return `${base} border-danger/70 hover:bg-danger/[0.06] dark:border-danger/80 dark:hover:bg-danger/[0.1] [&_svg]:text-danger`;
    }

    if (tone === 'warning') {
        return `${base} border-stock-near/70 hover:bg-stock-near/[0.06] dark:border-stock-near-dark/75 dark:hover:bg-stock-near-dark/[0.1] [&_svg]:text-stock-near dark:[&_svg]:text-stock-near-dark`;
    }

    if (tone === 'safe') {
        return `${base} border-success/55 hover:bg-success/[0.06] dark:border-success/65 dark:hover:bg-success/[0.1] [&_svg]:text-success`;
    }

    return `${base} border-border/80 [&_svg]:text-text-heading`;
}
