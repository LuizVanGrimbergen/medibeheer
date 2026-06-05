<script setup lang="ts">
import { computed } from 'vue';
import FamilyLinkedPatientsSection from '@/Components/Family/Overview/FamilyLinkedPatientsSection.vue';
import type { FamilyDashboardProps } from '@/lib/types';

const props = defineProps<{
    title: string;
    family?: FamilyDashboardProps;
    showActivePatient?: boolean;
    linkedPatientsHeadingKey?: string;
    linkedPatientsToggleKey?: string;
}>();

const shouldShowLinkedPatients = computed((): boolean => {
    if (props.family === undefined) {
        return false;
    }

    if (props.showActivePatient === false) {
        return false;
    }

    if (props.showActivePatient === true) {
        return true;
    }

    return props.family.has_linked_patient;
});
</script>

<template>
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 md:gap-5">
        <h1 class="sr-only">
            {{ title }}
        </h1>

        <FamilyLinkedPatientsSection
            v-if="shouldShowLinkedPatients"
            :family="family!"
            :heading-key="linkedPatientsHeadingKey"
            :toggle-key="linkedPatientsToggleKey"
        />

        <slot />
    </div>
</template>
