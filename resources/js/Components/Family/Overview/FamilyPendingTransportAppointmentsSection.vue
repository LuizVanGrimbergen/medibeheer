<script setup lang="ts">
import { Car } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CollapsibleSectionCard from '@/Components/ui/collapsible-section/CollapsibleSectionCard.vue';
import FamilyTransportAppointmentCard from '@/Components/Family/Overview/FamilyTransportAppointmentCard.vue';
import {
    acceptTransport,
    declineTransport,
} from '@/composables/family/useFamilyAppointmentsActions';
import type { FamilyPendingTransportAppointment } from '@/lib/family/overview/familyPendingTransportAppointments';

const props = defineProps<{
    appointments: FamilyPendingTransportAppointment[];
}>();

const { t } = useI18n();

const isOpen = ref(false);

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
</script>

<template>
    <CollapsibleSectionCard
        v-if="hasAppointments"
        v-model:open="isOpen"
        :heading="t('family.overview.transportPendingHeading')"
        :toggle-label="t('family.overview.transportPendingToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-stock-near/15 text-stock-near dark:bg-stock-near-dark/20 dark:text-stock-near-dark"
    >
        <template #icon>
            <Car :size="20" :stroke-width="1.75" />
        </template>

        <ul class="space-y-4 md:space-y-3">
            <li
                v-for="appointment in props.appointments"
                :key="appointment.invitation_id"
            >
                <FamilyTransportAppointmentCard
                    :appointment="appointment"
                    variant="pending"
                    :accept-url="appointment.accept_url"
                    :decline-url="appointment.decline_url"
                    @accept-transport="acceptTransport(appointment.accept_url)"
                    @decline-transport="
                        declineTransport(appointment.decline_url)
                    "
                />
            </li>
        </ul>
    </CollapsibleSectionCard>
</template>
