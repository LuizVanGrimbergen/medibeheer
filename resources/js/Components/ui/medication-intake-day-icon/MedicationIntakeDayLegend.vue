<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import MedicationIntakeDayIcon from '@/Components/ui/medication-intake-day-icon/MedicationIntakeDayIcon.vue';
import { MEDICATION_INTAKE_DAY_LEGEND_OPTIONS } from '@/lib/patient/medications/history/medicationIntakeDayPresentation';

const { t } = useI18n();
</script>

<template>
    <span
        v-for="option in MEDICATION_INTAKE_DAY_LEGEND_OPTIONS"
        :key="option.status"
        class="text-text-heading inline-flex items-center gap-1.5 font-medium"
    >
        <MedicationIntakeDayIcon :status="option.status" size="legend" />
        {{ t(option.labelKey) }}
    </span>
</template>
