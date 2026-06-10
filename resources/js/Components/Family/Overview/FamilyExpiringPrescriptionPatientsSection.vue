<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { FileText } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyExpiringPrescriptionPatientCard from '@/Components/Family/Overview/FamilyExpiringPrescriptionPatientCard.vue';
import CollapsibleSectionCard from '@/Components/ui/collapsible-section/CollapsibleSectionCard.vue';
import type { FamilyExpiringPrescriptionPatient } from '@/lib/family/overview/familyExpiringPrescriptionPatients';

const props = defineProps<{
    patients: FamilyExpiringPrescriptionPatient[];
}>();

const { t } = useI18n();

const isOpen = ref(false);

const hasPatients = computed(() => props.patients.length > 0);

const collapsedSummary = computed(() =>
    t('family.overview.prescriptionsCollapsedSummary', {
        count: String(props.patients.length),
    }),
);

function openPatientMedications(
    patient: FamilyExpiringPrescriptionPatient,
): void {
    router.post(
        patient.switch_url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(patient.medications_url);
            },
        },
    );
}
</script>

<template>
    <CollapsibleSectionCard
        v-if="hasPatients"
        v-model:open="isOpen"
        :heading="t('family.overview.prescriptionsHeading')"
        :toggle-label="t('family.overview.prescriptionsToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-danger/12 text-danger"
    >
        <template #icon>
            <FileText :size="20" :stroke-width="1.75" />
        </template>

        <ul class="space-y-4 md:space-y-3">
            <li v-for="patient in props.patients" :key="patient.patient_id">
                <FamilyExpiringPrescriptionPatientCard
                    :patient="patient"
                    @click="openPatientMedications(patient)"
                />
            </li>
        </ul>
    </CollapsibleSectionCard>
</template>
