<script setup lang="ts">
import ActivePatientBadge from '@/Components/Family/ActivePatientBadge.vue';
import type { FamilyDashboardProps } from '@/lib/types';

defineProps<{
    title: string;
    family?: FamilyDashboardProps;
    showActivePatient?: boolean;
}>();
</script>

<template>
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 md:gap-5">
        <h1 class="sr-only">
            {{ title }}
        </h1>

        <ActivePatientBadge
            v-if="showActivePatient && family !== undefined"
            :family="family"
            variant="compact"
        />

        <slot />
    </div>
</template>
