<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Car } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import FamilyOverviewCollapsibleSection from '@/Components/Family/Overview/FamilyOverviewCollapsibleSection.vue';
import FamilyTransportAppointmentCard from '@/Components/Family/Overview/FamilyTransportAppointmentCard.vue';
import type { FamilyAcceptedTransportAppointment } from '@/lib/family/overview/familyAcceptedTransportAppointments';

const props = defineProps<{
    appointments: FamilyAcceptedTransportAppointment[];
}>();

const { t } = useI18n();

const isOpen = ref(true);

const hasAppointments = computed(() => props.appointments.length > 0);

const collapsedSummary = computed(() => {
    const count = props.appointments.length;

    if (count === 1) {
        return t('family.overview.transportCollapsedSummaryOne');
    }

    return t('family.overview.transportCollapsedSummaryMany', {
        count: String(count),
    });
});

function openPatientAppointments(appointment: FamilyAcceptedTransportAppointment): void {
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
        :heading="t('family.overview.transportHeading')"
        :toggle-label="t('family.overview.transportToggle')"
        :collapsed-summary="collapsedSummary"
        icon-wrapper-class="bg-primary/12 text-primary"
    >
        <template #icon>
            <Car class="size-5" />
        </template>

        <ul class="space-y-4 md:space-y-3">
            <li
                v-for="appointment in props.appointments"
                :key="appointment.id"
            >
                <FamilyTransportAppointmentCard
                    :appointment="appointment"
                    variant="accepted"
                    @click="openPatientAppointments(appointment)"
                />
            </li>
        </ul>
    </FamilyOverviewCollapsibleSection>
</template>
