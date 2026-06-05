<script setup lang="ts">
import { patientFormWizardFooterRowClass } from '@/lib/patient/patientShellDialogLayout';
</script>

<template>
    <div :class="patientFormWizardFooterRowClass">
        <slot />
    </div>
</template>
