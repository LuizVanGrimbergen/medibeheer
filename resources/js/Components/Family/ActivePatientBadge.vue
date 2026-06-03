<script setup lang="ts">
import { computed } from 'vue';
import type { FamilyDashboardProps } from '@/lib/types';

const props = defineProps<{
    family: FamilyDashboardProps;
    variant?: 'badge' | 'inline' | 'compact';
}>();

const activePatientName = computed(() => {
    const patient = props.family.patients.find((p) => p.is_active);

    return patient?.name ?? null;
});
</script>

<template>
    <div
        v-if="
            props.family.patients.length > 0 &&
            props.family.active_patient_id !== null &&
            activePatientName
        "
        :class="
            props.variant === 'compact'
                ? 'border-border/70 bg-surface flex flex-wrap items-baseline gap-x-2 gap-y-0.5 rounded-xl border px-3 py-2 md:py-1.5'
                : props.variant === 'inline'
                  ? 'text-text-muted text-sm'
                  : 'border-border/70 bg-surface rounded-2xl border px-4 py-3'
        "
    >
        <p
            v-if="props.variant !== 'inline'"
            class="text-text-muted text-xs font-semibold tracking-wide uppercase"
        >
            {{ $t('family.overview.activePatientLabel') }}
        </p>
        <p
            class="leading-snug font-semibold"
            :class="
                props.variant === 'inline'
                    ? 'text-primary'
                    : props.variant === 'compact'
                      ? 'text-primary text-sm md:text-sm'
                      : 'text-primary mt-1 text-base'
            "
        >
            {{ activePatientName }}
        </p>
    </div>
</template>
