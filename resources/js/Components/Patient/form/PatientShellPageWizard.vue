<script setup lang="ts">
import {
    patientShellPageDescriptionClass,
    patientShellPageHeaderClass,
    patientShellPageRootClass,
    patientShellPageTitleClass,
} from '@/lib/patient/patientShellDialogLayout';

defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div :class="patientShellPageRootClass">
        <header :class="patientShellPageHeaderClass">
            <h1 :class="patientShellPageTitleClass">
                {{ title }}
            </h1>
            <p
                v-if="description !== undefined && description !== ''"
                :class="patientShellPageDescriptionClass"
            >
                {{ description }}
            </p>
        </header>

        <slot />
    </div>
</template>
