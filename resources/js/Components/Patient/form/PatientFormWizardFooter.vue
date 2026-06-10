<script setup lang="ts">
import { mobileShellFormWizardFooterRowClass } from '@/lib/shell/mobileShellDialogLayout';
</script>

<template>
    <div :class="mobileShellFormWizardFooterRowClass">
        <slot />
    </div>
</template>
