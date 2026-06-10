<script setup lang="ts">
import { computed } from 'vue';
import FamilyLinkedPatientsSection from '@/Components/Family/Overview/FamilyLinkedPatientsSection.vue';
import { mobileShellPageContentClass } from '@/lib/shell/mobileShellLayout';
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
    <div :class="mobileShellPageContentClass">
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
