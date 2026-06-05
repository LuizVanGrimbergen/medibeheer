<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, nextTick, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import AppointmentCard from '@/Components/Appointments/AppointmentCard.vue';
import AppointmentPairActionButtons from '@/Components/Appointments/AppointmentPairActionButtons.vue';
import FamilyPageShell from '@/Components/Family/FamilyPageShell.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { SegmentedToggle } from '@/Components/ui/segmented-toggle';
import {
    acceptTransport,
    declineTransport,
    setAppointmentViewFromToggle,
} from '@/composables/family/useFamilyAppointmentsActions';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type { FamilyAppointmentsScreenProps } from '@/lib/family/appointments/familyAppointmentsScreenProps';
import { readFamilyScreenQueryParam } from '@/lib/family/readFamilyScreenQueryParam';

const props = defineProps<FamilyAppointmentsScreenProps>();

const { t } = useI18n();
const page = usePage();

const paginationQuery = computed((): Record<string, string | number> => {
    const query: Record<string, string | number> = {
        view: props.appointment_view,
    };

    const appointment = readFamilyScreenQueryParam('appointment', page.url);

    if (appointment !== null) {
        query.appointment = appointment;
    }

    return query;
});

function onAppointmentViewUpdate(next: string): void {
    setAppointmentViewFromToggle(next, props.appointment_view);
}

function scrollToDeepLinkedAppointment(): void {
    const appointmentId = readFamilyScreenQueryParam('appointment', page.url);

    if (appointmentId === null) {
        return;
    }

    const id = Number(appointmentId);

    if (!props.appointments.data.some((appointment) => appointment.id === id)) {
        return;
    }

    nextTick(() => {
        document
            .getElementById(`family-appointment-${id}`)
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

watch(
    () => [page.url, props.appointments.data] as const,
    () => {
        scrollToDeepLinkedAppointment();
    },
    { immediate: true, deep: true },
);
</script>

<template>
    <Head>
        <title>{{ t('family.appointments.title') }}</title>
    </Head>

    <FamilyLayout>
        <FamilyPageShell
            :title="t('family.appointments.heading')"
            :family="props.family"
            :show-active-patient="props.family.has_linked_patient"
        >
            <div v-if="props.family.has_linked_patient">
                <SegmentedToggle
                    :model-value="props.appointment_view"
                    :options="[
                        {
                            value: 'planned',
                            label: t('family.appointments.plannedToggle'),
                            count: props.appointment_tab_totals.planned,
                        },
                        {
                            value: 'completed',
                            label: t('family.appointments.completedToggle'),
                            count: props.appointment_tab_totals.completed,
                        },
                    ]"
                    @update:model-value="onAppointmentViewUpdate"
                />
            </div>

            <p
                v-if="!props.family.has_linked_patient"
                class="text-text-muted max-w-prose text-sm leading-relaxed"
            >
                {{ t('family.appointments.notLinked') }}
            </p>

            <Card
                v-else-if="props.appointments.data.length === 0"
                class="border-border"
            >
                <CardContent class="text-text-muted py-10 text-sm">
                    {{
                        props.appointment_view === 'planned'
                            ? t('family.appointments.emptyPlanned')
                            : t('family.appointments.emptyCompleted')
                    }}
                </CardContent>
            </Card>

            <div v-else class="space-y-4">
                <div
                    v-for="appointment in props.appointments.data"
                    :key="appointment.id"
                    class="space-y-3"
                >
                    <AppointmentCard
                        :anchor-id="`family-appointment-${appointment.id}`"
                        details-toggle-variant="header"
                        :show-provider-subtitle="false"
                        :show-compact-google-maps-link="true"
                        :transport-label="
                            t('family.appointments.transportLabel')
                        "
                        :appointment="{
                            id: appointment.id,
                            doctor_type: appointment.doctor_type,
                            provider_name: appointment.provider_name,
                            street: appointment.street,
                            house_number: appointment.house_number,
                            postal_code: appointment.postal_code,
                            city: appointment.city,
                            starts_at: appointment.starts_at,
                            needs_transport: appointment.needs_transport,
                            transport_family: appointment.transport_family,
                            transport_status: appointment.transport_status,
                            doctor_visit_summary:
                                appointment.doctor_visit_summary,
                            cancellation_reason:
                                appointment.cancellation_reason,
                            status: appointment.status,
                        }"
                        :done-displayed="appointment.status === 'done'"
                        :is-patching="false"
                        :show-actions="false"
                        :show-done-toggle="false"
                        :show-transport-section="true"
                        :done-summary-label="
                            t('family.appointments.doneSummaryLabel')
                        "
                    >
                        <template #transport-actions>
                            <div
                                v-if="
                                    appointment.transport_invitation
                                        ?.accept_url &&
                                    appointment.status === 'scheduled'
                                "
                                class="flex justify-center pt-1"
                            >
                                <AppointmentPairActionButtons
                                    centered
                                    :show-secondary="
                                        Boolean(
                                            appointment.transport_invitation
                                                .decline_url,
                                        )
                                    "
                                    @primary-click="
                                        acceptTransport(
                                            appointment.transport_invitation
                                                .accept_url,
                                        )
                                    "
                                    @secondary-click="
                                        declineTransport(
                                            appointment.transport_invitation
                                                .decline_url,
                                        )
                                    "
                                >
                                    <template #primary>
                                        {{
                                            t(
                                                'family.appointments.acceptTransport',
                                            )
                                        }}
                                    </template>
                                    <template #secondary>
                                        {{
                                            t(
                                                'family.appointments.declineTransport',
                                            )
                                        }}
                                    </template>
                                </AppointmentPairActionButtons>
                            </div>
                        </template>
                    </AppointmentCard>
                </div>

                <NumberedPagination
                    v-if="props.appointments.meta.last_page > 1"
                    route-name="family.appointments"
                    :meta="props.appointments.meta"
                    :query="paginationQuery"
                />
            </div>
        </FamilyPageShell>
    </FamilyLayout>
</template>
