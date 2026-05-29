import type { LucideIcon } from 'lucide-vue-next';
import { Clock, UtensilsCrossed } from 'lucide-vue-next';
import type { MedicationMealTimingValue } from '@/lib/types';
import { MEDICATION_MEAL_TIMING_VALUES } from '@/lib/types';

export type MedicationMealTimingOptionVisual =
    | { kind: 'lucide'; icon: LucideIcon }
    | { kind: 'image'; src: string };

export type MedicationMealTimingOption = {
    timing: MedicationMealTimingValue;
    visual: MedicationMealTimingOptionVisual;
};

export const MEDICATION_MEAL_TIMING_OPTIONS: MedicationMealTimingOption[] =
    MEDICATION_MEAL_TIMING_VALUES.map((timing) => {
        if (timing === 'before_food') {
            return {
                timing,
                visual: { kind: 'image', src: '/images/before-dinner.svg' },
            };
        }

        if (timing === 'after_food') {
            return {
                timing,
                visual: { kind: 'image', src: '/images/after-dinner.svg' },
            };
        }

        if (timing === 'with_food') {
            return { timing, visual: { kind: 'lucide', icon: UtensilsCrossed } };
        }

        return { timing, visual: { kind: 'lucide', icon: Clock } };
    });
