<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MedicationUrgencyProgressSection from '@/Components/Medications/MedicationUrgencyProgressSection.vue';
import { prescriptionExpiryStatusLine } from '@/lib/patient/prescriptions/prescriptionExpiryStatusLine';
import { prescriptionExpiryUrgencyContext } from '@/lib/patient/prescriptions/prescriptionExpiryUrgency';

const props = defineProps<{
    prescriptionExpiryDate: string | null;
}>();

const { t } = useI18n();

const urgencyContext = computed(() =>
    prescriptionExpiryUrgencyContext(props.prescriptionExpiryDate),
);

const statusLine = computed((): string => {
    const context = urgencyContext.value;

    if (context === null) {
        return t('patient.prescriptions.prescriptionExpiryMissing');
    }

    return prescriptionExpiryStatusLine(t, context.days_remaining);
});
</script>

<template>
    <MedicationUrgencyProgressSection
        v-if="urgencyContext !== null"
        :tone="urgencyContext.tone"
        :progress-percent="null"
        progressAriaLabel=""
        :show-progress-bar="false"
        :status-line="statusLine"
        :critical-alert-label="t('patient.prescriptions.expiredBadge')"
        :warning-alert-label="t('patient.prescriptions.expiryWarningAria')"
    />

    <p
        v-else
        class="text-text-heading text-lg leading-relaxed font-semibold sm:text-xl"
    >
        {{ statusLine }}
    </p>
</template>
