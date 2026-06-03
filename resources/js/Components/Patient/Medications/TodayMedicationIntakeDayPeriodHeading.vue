<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';
import { Moon, Sun, Sunrise, Sunset } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { TodayMedicationIntakeDayPeriodValue } from '@/lib/types';

const periodIcons: Record<TodayMedicationIntakeDayPeriodValue, LucideIcon> = {
    morning: Sunrise,
    afternoon: Sun,
    evening: Sunset,
    night: Moon,
};

const props = defineProps<{
    period: TodayMedicationIntakeDayPeriodValue;
}>();

const { t } = useI18n();

const title = computed(() =>
    t(`patient.dashboard.todayMedications.periods.${props.period}.title`),
);

const hint = computed(() =>
    t(`patient.dashboard.todayMedications.periods.${props.period}.hint`),
);

const icon = computed(() => periodIcons[props.period]);
</script>

<template>
    <div class="flex items-start gap-2.5 px-0.5 sm:gap-3">
        <component
            :is="icon"
            class="text-primary mt-0.5 size-6 shrink-0 sm:mt-0 sm:size-7"
            aria-hidden="true"
        />
        <div class="min-w-0 space-y-1">
            <h3 class="text-text-heading text-lg font-bold sm:text-xl">
                {{ title }}
            </h3>
            <p class="text-text-muted text-base sm:text-lg">
                {{ hint }}
            </p>
        </div>
    </div>
</template>
