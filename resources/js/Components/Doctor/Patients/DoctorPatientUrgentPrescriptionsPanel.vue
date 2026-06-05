<script setup lang="ts">
import { FileText } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import DoctorCollapsibleSection from '@/Components/Doctor/Patients/DoctorCollapsibleSection.vue';
import MedicationUrgentPrescriptionStatusCard from '@/Components/Medications/MedicationUrgentPrescriptionStatusCard.vue';
import type { DoctorPatientUrgentPrescription } from '@/lib/doctor/patients/doctorPatientUrgentPrescription';

const props = defineProps<{
    prescriptions: DoctorPatientUrgentPrescription[];
}>();

const { t } = useI18n();

const isOpen = ref(false);

const collapsedSummary = computed((): string => {
    const count = props.prescriptions.length;

    if (count === 1) {
        return t('doctor.patients.prescriptionsCollapsedOne');
    }

    return t('doctor.patients.prescriptionsCollapsedMany', {
        count: String(count),
    });
});
</script>

<template>
    <DoctorCollapsibleSection
        v-if="props.prescriptions.length > 0"
        v-model:open="isOpen"
        :heading="t('doctor.patients.prescriptionsHeading')"
        :toggle-label="t('doctor.patients.prescriptionsToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-danger/12 text-danger"
    >
        <template #icon>
            <FileText class="size-5" />
        </template>

        <div class="space-y-4">
            <MedicationUrgentPrescriptionStatusCard
                v-for="prescription in props.prescriptions"
                :key="prescription.id"
                :prescription="prescription"
            />
        </div>
    </DoctorCollapsibleSection>
</template>
