<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, defineAsyncComponent, nextTick, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyMedicationReminderPrompt from '@/Components/Family/FamilyMedicationReminderPrompt.vue';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import FamilyAcceptedTransportAppointmentsSection from '@/Components/Family/Overview/FamilyAcceptedTransportAppointmentsSection.vue';
import FamilyExpiringPrescriptionPatientsSection from '@/Components/Family/Overview/FamilyExpiringPrescriptionPatientsSection.vue';
import FamilyLowStockPatientsSection from '@/Components/Family/Overview/FamilyLowStockPatientsSection.vue';
import FamilyOverviewUpdatesSection from '@/Components/Family/Overview/FamilyOverviewUpdatesSection.vue';
import FamilyPendingTransportAppointmentsSection from '@/Components/Family/Overview/FamilyPendingTransportAppointmentsSection.vue';
import ListCardSkeleton from '@/Components/ui/skeleton/ListCardSkeleton.vue';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import { ensureLaravelEchoIsConfigured } from '@/lib/configureLaravelEcho';
import type { FamilyOverviewScreenProps } from '@/lib/family/overview/familyAcceptedTransportAppointments';
import { familyOverviewUpdatesOpenQuery } from '@/lib/family/familyOverviewDeepLinkQuery';
import { readScreenQueryFlag } from '@/lib/inertia/readNumericScreenQueryParam';
import { areAnyDeferredInertiaPropsLoading } from '@/lib/inertia/isDeferredInertiaPropLoading';
import type { FamilyDashboardProps, PageProps } from '@/lib/types';

const FamilyUpdatesEchoListener = defineAsyncComponent(
    () => import('@/Components/Family/Updates/FamilyUpdatesEchoListener.vue'),
);

type PageWithFamily = PageProps & { family?: FamilyDashboardProps };

const props = defineProps<FamilyOverviewScreenProps>();

const { t } = useI18n();
const page = usePage<PageWithFamily>();

const family = computed(() => page.props.family);

const echoReady = ref(false);

const openUpdatesFromDeepLink = readScreenQueryFlag(
    familyOverviewUpdatesOpenQuery.name,
    familyOverviewUpdatesOpenQuery.value,
    page.url,
);

function revealUpdatesFromDeepLink(): void {
    if (!openUpdatesFromDeepLink) {
        return;
    }

    nextTick(() => {
        document
            .getElementById('family-overview-updates')
            ?.scrollIntoView({ behavior: 'smooth', block: 'start' });

        const url = new URL(globalThis.location.href);
        url.searchParams.delete(familyOverviewUpdatesOpenQuery.name);

        const query = url.searchParams.toString();
        const nextUrl =
            query.length > 0 ? `${url.pathname}?${query}` : url.pathname;

        globalThis.history.replaceState({}, '', nextUrl);
    });
}

onMounted(async () => {
    revealUpdatesFromDeepLink();
    await ensureLaravelEchoIsConfigured();
    echoReady.value = true;
});

const activePatientId = computed(
    (): number | null => family.value?.active_patient_id ?? null,
);

const isOverviewLoading = computed(() =>
    areAnyDeferredInertiaPropsLoading(
        props.low_stock_patients,
        props.expiring_prescription_patients,
        props.updates_checkins,
        props.updates_medication_intakes,
        props.pending_transport_appointments,
        props.accepted_transport_appointments,
    ),
);
</script>

<template>
    <Head>
        <title>{{ t('family.overview.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyUpdatesEchoListener
            v-if="
                echoReady &&
                family?.has_linked_patient &&
                activePatientId !== null
            "
            :key="activePatientId"
            :patient-id="activePatientId"
        />

        <FamilyPageShell :title="t('family.overview.heading')" :family="family">
            <FamilyMedicationReminderPrompt />

            <ListCardSkeleton v-if="isOverviewLoading" />

            <template v-else>
                <FamilyLowStockPatientsSection
                    :patients="props.low_stock_patients ?? []"
                />

                <FamilyExpiringPrescriptionPatientsSection
                    :patients="props.expiring_prescription_patients ?? []"
                />

                <FamilyPendingTransportAppointmentsSection
                    :appointments="props.pending_transport_appointments ?? []"
                />

                <FamilyAcceptedTransportAppointmentsSection
                    :appointments="
                        props.accepted_transport_appointments ?? []
                    "
                />

                <FamilyOverviewUpdatesSection
                    v-if="family?.has_linked_patient"
                    :checkins="props.updates_checkins ?? []"
                    :medication-intakes="props.updates_medication_intakes ?? []"
                    :default-open="openUpdatesFromDeepLink"
                />
            </template>
        </FamilyPageShell>
    </FamilyLayout>
</template>
