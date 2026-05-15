import { jar } from '@lucide/lab';
import type { LucideIcon } from 'lucide-vue-next';
import {
    createLucideIcon,
    Droplets,
    MoreHorizontal,
    Package2,
    Pill,
    Syringe,
} from 'lucide-vue-next';
import type { MedicationTypeValue } from '@/lib/types';
import { MEDICATION_TYPE_VALUES } from '@/lib/types';

const MedicationCreamJarIcon = createLucideIcon('MedicationCreamJar', jar);

const MEDICATION_TYPE_ICON: Record<MedicationTypeValue, LucideIcon> = {
    pill: Pill,
    liquid: Droplets,
    injection: Syringe,
    cream: MedicationCreamJarIcon,
    sachets: Package2,
    other: MoreHorizontal,
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
