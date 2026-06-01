import type { MedicationStockProgressTone } from '@/lib/patient/inventory/medicationListVisualTone';

const panelBase =
    'flex min-w-0 w-full items-start gap-3.5 rounded-2xl border px-4 py-3.5 sm:gap-4 sm:rounded-3xl sm:px-5 sm:py-4';

const iconWrapBase =
    'flex size-11 shrink-0 items-center justify-center rounded-xl sm:size-14 sm:rounded-2xl';

export function medicationStockCurrentStockPanelClass(
    tone: MedicationStockProgressTone | null,
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

export function medicationStockCurrentStockIconWrapClass(
    tone: MedicationStockProgressTone | null,
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
