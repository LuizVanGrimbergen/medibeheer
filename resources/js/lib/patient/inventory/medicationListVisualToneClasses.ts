import type { MedicationStockProgressTone } from '@/lib/patient/inventory/medicationStockProgressTone';

export type MedicationListVisualToneClassSet = {
    border: string;
    pillWrap: string;
    pillIcon: string;
};

const MEDICATION_LIST_VISUAL_TONE_CLASSES: Record<
    MedicationStockProgressTone,
    MedicationListVisualToneClassSet
> = {
    critical: {
        border: 'border-danger/70 dark:border-danger/80',
        pillWrap: 'bg-danger/12',
        pillIcon: 'text-danger',
    },
    warning: {
        border: 'border-stock-near/70 dark:border-stock-near-dark/75',
        pillWrap: 'bg-stock-near/12 dark:bg-stock-near-dark/15',
        pillIcon: 'text-stock-near dark:text-stock-near-dark',
    },
    safe: {
        border: 'border-success/55 dark:border-success/65',
        pillWrap: 'bg-success/12',
        pillIcon: 'text-success',
    },
};

const MEDICATION_LIST_VISUAL_TONE_NEUTRAL: MedicationListVisualToneClassSet = {
    border: 'border-border/80',
    pillWrap: 'bg-primary/12',
    pillIcon: 'text-primary',
};

export function medicationListVisualToneClasses(
    tone: MedicationStockProgressTone | null,
): MedicationListVisualToneClassSet {
    if (tone === null) {
        return MEDICATION_LIST_VISUAL_TONE_NEUTRAL;
    }

    return MEDICATION_LIST_VISUAL_TONE_CLASSES[tone];
}
