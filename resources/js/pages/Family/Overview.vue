<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyAcceptedTransportAppointmentsSection from '@/Components/Family/Overview/FamilyAcceptedTransportAppointmentsSection.vue';
import FamilyLinkedPatientsSection from '@/Components/Family/Overview/FamilyLinkedPatientsSection.vue';
import FamilyLowStockPatientsSection from '@/Components/Family/Overview/FamilyLowStockPatientsSection.vue';
import FamilyPendingTransportAppointmentsSection from '@/Components/Family/Overview/FamilyPendingTransportAppointmentsSection.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyOverviewScreenProps } from '@/lib/family/overview/familyAcceptedTransportAppointments';
import type { FamilyDashboardProps } from '@/lib/types';

const props = defineProps<
    FamilyOverviewScreenProps & {
        family: FamilyDashboardProps;
    }
>();

const { t } = useI18n();

const lowStockPatientIds = computed(() =>
    props.low_stock_patients.map((patient) => patient.patient_id),
);
</script>

<template>
    <Head>
        <title>{{ t('family.overview.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyPageShell :title="t('family.overview.heading')">
            <FamilyLinkedPatientsSection
                :family="props.family"
                :low-stock-patient-ids="lowStockPatientIds"
            />

            <FamilyLowStockPatientsSection :patients="props.low_stock_patients" />

            <FamilyPendingTransportAppointmentsSection
                :appointments="props.pending_transport_appointments"
            />

            <FamilyAcceptedTransportAppointmentsSection
                :appointments="props.accepted_transport_appointments"
            />

        </FamilyPageShell>
    </FamilyLayout>
</template>
