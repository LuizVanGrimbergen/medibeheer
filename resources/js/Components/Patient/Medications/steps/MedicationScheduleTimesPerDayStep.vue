<script setup lang="ts">
/* eslint-disable vue/no-mutating-props */
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFormCountPresetPicker from '@/Components/Patient/form/PatientFormCountPresetPicker.vue';
import type { MedicationCreateFormWithErrors } from '@/Components/Patient/Medications/form/MedicationFormTypes';

const { form, idPrefix } = defineProps<{
    form: MedicationCreateFormWithErrors;
    idPrefix: string;
}>();

const { t } = useI18n();

const timesPerDayCount = computed({
    get(): number {
        const trimmed = form.schedule.times_per_day.trim();

        if (!/^\d+$/.test(trimmed)) {
            return 1;
        }

        const parsed = Number(trimmed);

        if (!Number.isInteger(parsed) || parsed < 1 || parsed > 24) {
            return 1;
        }

        return parsed;
    },
    set(value: number): void {
        form.schedule.times_per_day = String(value);
    },
});

function timesPerDayOptionLabel(count: number): string {
    return t('patient.medications.timesPerDay.nTimesPerDay', { n: count });
}
</script>

<template>
    <PatientFormCountPresetPicker
        :id-prefix="`${idPrefix}-schedule-times-per-day`"
        v-model="timesPerDayCount"
        :label="t('patient.medications.fields.timesPerDay')"
        required
        :error-message="form.errors['schedule.times_per_day']"
        :option-label="timesPerDayOptionLabel"
        :custom-trigger-label="t('patient.medications.intakePeriodPresets.custom')"
        :custom-placeholder="t('patient.medications.timesPerDay.customPlaceholder')"
        :custom-select-aria-label="t('patient.medications.timesPerDay.customSelectAriaLabel')"
    />
</template>
