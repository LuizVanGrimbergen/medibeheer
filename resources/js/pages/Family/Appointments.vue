<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppointmentCard from '@/Components/Appointments/AppointmentCard.vue';
import AppointmentPairActionButtons from '@/Components/Appointments/AppointmentPairActionButtons.vue';
import ActivePatientBadge from '@/Components/Family/ActivePatientBadge.vue';
import { Card, CardContent } from '@/Components/ui/card';
import NumberedPagination from '@/Components/ui/pagination/NumberedPagination.vue';
import { SegmentedToggle } from '@/Components/ui/segmented-toggle';
import {
    acceptTransport,
    declineTransport,
    setAppointmentViewFromToggle,
} from '@/composables/useFamilyAppointmentsActions';
import FamilyLayout from '@/Layouts/FamilyLayout.vue';
import type {
    FamilyAppointmentsScreenProps,
} from '@/lib/family/appointments/familyAppointmentsScreenProps';

const props = defineProps<FamilyAppointmentsScreenProps>();

const { t } = useI18n();

function onAppointmentViewUpdate(next: string): void {
    setAppointmentViewFromToggle(next, props.appointment_view);
}
</script>

<template>
    <Head>
        <title>{{ t('family.appointments.title') }}</title>
    </Head>

    <FamilyLayout>
        <div class="flex flex-col gap-6">
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold text-text-heading">
                    {{ t('family.appointments.heading') }}
                </h1>
                <ActivePatientBadge :family="props.family" />
            </div>

            <div
                v-if="props.family.has_linked_patient"
            >
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
                class="max-w-prose text-sm leading-relaxed text-text-muted"
            >
                {{ t('family.appointments.notLinked') }}
            </p>

            <Card
                v-else-if="props.appointments.data.length === 0"
                class="border-border"
            >
                <CardContent class="py-10 text-sm text-text-muted">
                    {{
                        props.appointment_view === 'planned'
                            ? t('family.appointments.emptyPlanned')
                            : t('family.appointments.emptyCompleted')
                    }}
                </CardContent>
            </Card>

            <div
                v-else
                class="space-y-4"
            >
                <div
                    v-for="appointment in props.appointments.data"
                    :key="appointment.id"
                    class="space-y-3"
                >
                    <AppointmentCard
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
                            doctor_visit_summary: appointment.doctor_visit_summary,
                            cancellation_reason: appointment.cancellation_reason,
                            status: appointment.status,
                        }"
                        :done-displayed="appointment.status === 'done'"
                        :is-patching="false"
                        :show-actions="false"
                        :show-done-toggle="false"
                        :done-summary-label="t('family.appointments.doneSummaryLabel')"
                    >
                        <template #transport-actions>
                            <div
                                v-if="appointment.transport_invitation?.accept_url && appointment.status === 'scheduled'"
                                class="pt-1"
                            >
                                <AppointmentPairActionButtons
                                    :show-secondary="
                                        Boolean(appointment.transport_invitation.decline_url)
                                    "
                                    @primary-click="
                                        acceptTransport(
                                            appointment.transport_invitation.accept_url,
                                        )
                                    "
                                    @secondary-click="
                                        declineTransport(
                                            appointment.transport_invitation.decline_url,
                                        )
                                    "
                                >
                                    <template #primary>
                                        {{ t('family.appointments.acceptTransport') }}
                                    </template>
                                    <template #secondary>
                                        {{ t('family.appointments.declineTransport') }}
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
                    :query="{ view: props.appointment_view }"
                />
            </div>
        </div>
    </FamilyLayout>
</template>
