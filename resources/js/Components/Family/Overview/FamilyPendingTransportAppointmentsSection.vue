<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Car } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyOverviewCollapsibleSection from '@/Components/Family/Overview/FamilyOverviewCollapsibleSection.vue';
import FamilyTransportAppointmentCard from '@/Components/Family/Overview/FamilyTransportAppointmentCard.vue';
import type { FamilyPendingTransportAppointment } from '@/lib/family/overview/familyPendingTransportAppointments';

const props = defineProps<{
    appointments: FamilyPendingTransportAppointment[];
}>();

const { t } = useI18n();

const isOpen = ref(true);

const hasAppointments = computed(() => props.appointments.length > 0);

const collapsedSummary = computed(() => {
    const count = props.appointments.length;

    if (count === 1) {
        return t('family.overview.transportPendingCollapsedSummaryOne');
    }

    return t('family.overview.transportPendingCollapsedSummaryMany', {
        count: String(count),
    });
});

function openPatientAppointments(appointment: FamilyPendingTransportAppointment): void {
    router.post(
        appointment.switch_url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(appointment.appointments_url);
            },
        },
    );
}
</script>

<template>
    <FamilyOverviewCollapsibleSection
        v-if="hasAppointments"
        v-model:open="isOpen"
        :heading="t('family.overview.transportPendingHeading')"
        :toggle-label="t('family.overview.transportPendingToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-stock-near/15 text-stock-near dark:bg-stock-near-dark/20 dark:text-stock-near-dark"
    >
        <template #icon>
            <Car class="size-5" />
        </template>

        <ul class="space-y-4 md:space-y-3">
            <li
                v-for="appointment in props.appointments"
                :key="appointment.invitation_id"
            >
                <FamilyTransportAppointmentCard
                    :appointment="appointment"
                    variant="pending"
                    @click="openPatientAppointments(appointment)"
                />
            </li>
        </ul>
    </FamilyOverviewCollapsibleSection>
</template>
