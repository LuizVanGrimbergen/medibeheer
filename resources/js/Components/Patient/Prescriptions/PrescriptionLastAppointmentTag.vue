<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <p class="text-danger text-base leading-snug font-semibold sm:text-lg">
        {{ t('patient.prescriptions.lastPrescriptionAppointmentTag') }}
    </p>
</template>
