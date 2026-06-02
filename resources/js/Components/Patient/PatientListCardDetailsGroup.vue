<script setup lang="ts">
import { patientPageCardDetailsGroupClass } from '@/lib/patient/patientPageTypography';
</script>

<template>
    <div :class="patientPageCardDetailsGroupClass">
        <slot />
    </div>
</template>
