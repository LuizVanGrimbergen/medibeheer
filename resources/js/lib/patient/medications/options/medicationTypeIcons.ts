import type { LucideIcon } from 'lucide-vue-next';
import {
    Droplets,
    MoreHorizontal,
    Package2,
    Pill,
    Sparkles,
    Syringe,
} from 'lucide-vue-next';
import type { MedicationTypeValue } from '@/lib/types';
import { MEDICATION_TYPE_VALUES } from '@/lib/types';

const MEDICATION_TYPE_ICON: Record<MedicationTypeValue, LucideIcon> = {
    pill: Pill,
    liquid: Droplets,
    injection: Syringe,
    cream: Sparkles,
    sachets: Package2,
    other: MoreHorizontal,
};

export const MEDICATION_TYPE_OPTIONS: {
    type: MedicationTypeValue;
    icon: LucideIcon;
}[] = MEDICATION_TYPE_VALUES.map((type) => ({
    type,
    icon: MEDICATION_TYPE_ICON[type],
}));
