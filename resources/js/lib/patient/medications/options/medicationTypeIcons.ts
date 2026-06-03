import type { LucideIcon } from 'lucide-vue-next';
import { Droplets, Package2, Pill, Syringe } from 'lucide-vue-next';
import type { MedicationTypeValue } from '@/lib/types';
import { MEDICATION_TYPE_VALUES } from '@/lib/types';

const MEDICATION_TYPE_ICON: Record<MedicationTypeValue, LucideIcon> = {
    pill: Pill,
    liquid: Droplets,
    injection: Syringe,
    sachets: Package2,
};

export const medicationTypeIcon = (type: MedicationTypeValue): LucideIcon =>
    MEDICATION_TYPE_ICON[type];

export const MEDICATION_TYPE_OPTIONS: {
    type: MedicationTypeValue;
    icon: LucideIcon;
}[] = MEDICATION_TYPE_VALUES.map((type) => ({
    type,
    icon: MEDICATION_TYPE_ICON[type],
}));
