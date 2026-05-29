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
        v-if="props.family.patients.length > 0 && props.family.active_patient_id !== null && activePatientName"
        :class="
            props.variant === 'compact'
                ? 'flex flex-wrap items-baseline gap-x-2 gap-y-0.5 rounded-xl border border-border/70 bg-surface px-3 py-2 md:py-1.5'
                : props.variant === 'inline'
                  ? 'text-sm text-text-muted'
                  : 'rounded-2xl border border-border/70 bg-surface px-4 py-3'
        "
    >
        <p
            v-if="props.variant !== 'inline'"
            class="text-xs font-semibold uppercase tracking-wide text-text-muted"
        >
            {{ $t('family.overview.activePatientLabel') }}
        </p>
        <p
            class="font-semibold leading-snug"
            :class="
                props.variant === 'inline'
                    ? 'text-primary'
                    : props.variant === 'compact'
                      ? 'text-sm text-primary md:text-sm'
                      : 'mt-1 text-base text-primary'
            "
        >
            {{ activePatientName }}
        </p>
    </div>
</template>

