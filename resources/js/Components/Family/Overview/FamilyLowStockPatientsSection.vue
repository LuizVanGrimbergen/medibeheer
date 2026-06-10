<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyLowStockPatientCard from '@/Components/Family/Overview/FamilyLowStockPatientCard.vue';
import CollapsibleSectionCard from '@/Components/ui/collapsible-section/CollapsibleSectionCard.vue';
import type { FamilyLowStockPatient } from '@/lib/family/overview/familyLowStockPatients';

const props = defineProps<{
    patients: FamilyLowStockPatient[];
}>();

const { t } = useI18n();

const isOpen = ref(false);

const hasPatients = computed(() => props.patients.length > 0);

const collapsedSummary = computed(() =>
    t('family.overview.lowStockCollapsedSummary', {
        count: String(props.patients.length),
    }),
);

function openPatientMedications(patient: FamilyLowStockPatient): void {
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
        :heading="t('family.overview.lowStockHeading')"
        :toggle-label="t('family.overview.lowStockToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-danger/12 text-danger"
    >
        <template #icon>
            <AlertTriangle :size="20" :stroke-width="1.75" />
        </template>

        <ul class="space-y-4 md:space-y-3">
            <li v-for="patient in props.patients" :key="patient.patient_id">
                <FamilyLowStockPatientCard
                    :patient="patient"
                    @click="openPatientMedications(patient)"
                />
            </li>
        </ul>
    </CollapsibleSectionCard>
</template>
