<script setup lang="ts">
import {
    Calendar,
    Car,
    CheckCircle2,
    CircleX,
    Clock,
    MapPin,
    Pencil,
    Stethoscope,
    Trash2,
} from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AppointmentDoneToggle from '@/Components/Appointments/AppointmentDoneToggle.vue';
import { useAppointmentDisplay } from '@/Components/Appointments/useAppointmentDisplay';
import { Card, CardContent } from '@/Components/ui/card';
import { IconActionButton } from '@/Components/ui/icon-action-button';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import type {
    AppointmentTransportStatusValue,
    AppointmentStatusValue,
} from '@/lib/types';

type AppointmentCardAppointment = {
    id: number;
    doctor_type?: string;
    provider_name: string;
    street: string;
    house_number: string;
    postal_code: string;
    city: string;
    starts_at: string;
    needs_transport?: boolean;
    transport_status?: AppointmentTransportStatusValue | null;
    transport_family?: { id: number; name: string } | null;
    notes?: string | null;
    doctor_visit_summary?: string | null;
    cancellation_reason?: string | null;
    status?: AppointmentStatusValue;
};

defineProps<{
    appointment: AppointmentCardAppointment;
    doneDisplayed: boolean;
    isPatching: boolean;
    showActions?: boolean;
    showDoneToggle?: boolean;
    showTransportSection?: boolean;
    doneSummaryLabel?: string;
    completeFormHref?: string;
    cancelFormHref?: string;
}>();

const emit = defineEmits<{
    edit: [];
    delete: [];
    'update:done': [done: boolean];
}>();

const { t } = useI18n();
const { formatDateOnly, formatTimeOnly, doctorTypeLabel } =
    useAppointmentDisplay();
</script>

<template>
    <Card
        class="min-w-0 w-full rounded-3xl border border-border/80 bg-surface text-text shadow-md shadow-black/[0.04]"
    >
        <CardContent class="space-y-6 p-6 sm:p-7">
            <div class="space-y-4">
                <div class="flex min-w-0 items-start gap-4">
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/12"
                        aria-hidden="true"
                    >
                        <Stethoscope class="size-6 text-primary" />
                    </div>
                    <div class="min-w-0 flex-1 overflow-hidden space-y-1">
                        <p
                            class="text-lg font-bold leading-snug text-text-heading sm:text-xl"
                        >
                            {{
                                appointment.doctor_type
                                    ? doctorTypeLabel(appointment.doctor_type)
                                    : appointment.provider_name
                            }}
                        </p>
                        <p
                            v-if="appointment.doctor_type"
                            class="text-base font-normal leading-snug text-text-muted"
                        >
                            {{ appointment.provider_name }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="showActions ?? true"
                    class="flex justify-end gap-0.5 pt-1"
                >
                    <IconActionButton
                        v-if="appointment.status !== 'cancelled'"
                        :ariaLabel="t('patient.appointments.actions.edit')"
                        @click="emit('edit')"
                    >
                        <Pencil
                            class="size-5"
                            aria-hidden="true"
                        />
                    </IconActionButton>
                    <IconActionButton
                        tone="danger"
                        :ariaLabel="t('patient.appointments.actions.delete')"
                        @click="emit('delete')"
                    >
                        <Trash2
                            class="size-5"
                            aria-hidden="true"
                        />
                    </IconActionButton>
                </div>
            </div>

            <div class="space-y-5">
                <div
                    v-if="appointment.status === 'done' || appointment.status === 'cancelled'"
                    class="flex gap-4 sm:gap-5"
                >
                    <CheckCircle2
                        v-if="appointment.status === 'done'"
                        class="mt-0.5 size-6 shrink-0 text-primary"
                        :stroke-width="2"
                        aria-hidden="true"
                    />
                    <CircleX
                        v-else
                        class="mt-0.5 size-6 shrink-0 text-primary"
                        :stroke-width="2"
                        aria-hidden="true"
                    />
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <p
                            class="text-base font-semibold leading-tight text-text-heading"
                        >
                            {{ t('patient.appointments.fields.status') }}
                        </p>
                        <p
                            class="text-lg font-medium leading-relaxed tracking-tight sm:text-xl sm:leading-snug"
                            :class="
                                appointment.status === 'done'
                                    ? 'text-success'
                                    : 'text-danger'
                            "
                        >
                            {{
                                appointment.status === 'done'
                                    ? t('patient.appointments.statuses.done')
                                    : t('patient.appointments.statuses.cancelled')
                            }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-4 sm:gap-5">
                    <Calendar
                        class="mt-0.5 size-6 shrink-0 text-primary"
                        :stroke-width="2"
                        aria-hidden="true"
                    />
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <p class="text-base font-semibold leading-tight text-text-heading">
                            {{ t('patient.appointments.labels.when') }}
                        </p>
                        <p
                            class="text-lg font-medium leading-relaxed tracking-tight text-text sm:text-xl sm:leading-snug"
                        >
                            {{ formatDateOnly(appointment.starts_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-4 sm:gap-5">
                    <Clock
                        class="mt-0.5 size-6 shrink-0 text-primary"
                        :stroke-width="2"
                        aria-hidden="true"
                    />
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <p class="text-base font-semibold leading-tight text-text-heading">
                            {{ t('patient.appointments.labels.time') }}
                        </p>
                        <p
                            class="text-lg font-medium leading-relaxed text-text sm:text-xl sm:leading-snug"
                        >
                            {{ formatTimeOnly(appointment.starts_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-4 sm:gap-5">
                    <MapPin
                        class="mt-1 size-6 shrink-0 self-start text-primary"
                        :stroke-width="2"
                        aria-hidden="true"
                    />
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <p class="text-base font-semibold leading-tight text-text-heading">
                            {{ t('patient.appointments.labels.where') }}
                        </p>
                        <p
                            class="text-lg font-medium leading-relaxed text-text wrap-break-word text-pretty sm:text-xl sm:leading-snug"
                        >
                            {{ formatAppointmentAddress(appointment) }}
                        </p>
                    </div>
                </div>
                <div
                    v-if="showTransportSection ?? true"
                    class="flex gap-4 sm:gap-5"
                >
                    <Car
                        class="mt-1 size-6 shrink-0 self-start text-primary"
                        :stroke-width="2"
                        aria-hidden="true"
                    />
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <p class="text-base font-semibold leading-tight text-text-heading">
                            {{ t('patient.appointments.labels.transport') }}
                        </p>
                        <p
                            class="text-lg font-medium leading-relaxed sm:text-xl sm:leading-snug"
                            :class="
                                appointment.needs_transport
                                    ? appointment.transport_status === 'accepted'
                                        ? 'text-text'
                                        : appointment.transport_status === 'declined'
                                            ? 'text-danger'
                                            : 'text-text-muted'
                                    : 'text-text-muted'
                            "
                        >
                            {{
                                appointment.needs_transport
                                    ? appointment.transport_status === 'accepted'
                                        ? t('patient.appointments.transport.acceptedBy', {
                                            name: appointment.transport_family?.name ?? '',
                                        })
                                        : appointment.transport_status === 'declined'
                                            ? t('patient.appointments.transport.declined')
                                            : t('patient.appointments.transport.requested')
                                    : t('patient.appointments.transport.notNeeded')
                            }}
                        </p>
                        <slot name="transport-actions" />
                    </div>
                </div>
            </div>

            <p
                v-if="appointment.notes"
                class="border-l-[3px] border-primary py-0.5 pl-3.5 text-base italic leading-relaxed text-text sm:text-lg"
            >
                {{ appointment.notes }}
            </p>

            <div
                v-if="appointment.status === 'done' && appointment.doctor_visit_summary"
                class="space-y-2 border-l-[3px] border-success/50 py-0.5 pl-3.5"
            >
                <p class="text-base font-semibold leading-snug text-text-heading">
                    {{ doneSummaryLabel ?? t('patient.appointments.labels.afterVisit') }}
                </p>
                <p class="text-base leading-relaxed text-text sm:text-lg">
                    {{ appointment.doctor_visit_summary }}
                </p>
            </div>

            <AppointmentDoneToggle
                v-if="
                    (showDoneToggle ?? true) &&
                        appointment.status !== 'cancelled' &&
                        completeFormHref &&
                        cancelFormHref
                "
                :model-value="doneDisplayed"
                :disabled="isPatching"
                :complete-form-href="completeFormHref"
                :cancel-form-href="cancelFormHref"
                @update:model-value="emit('update:done', $event)"
            />

            <div
                v-else-if="appointment.status === 'cancelled'"
                class="space-y-3 border-t border-border/70 pt-5"
            >
                <p
                    class="border-l-[3px] border-text-muted/35 py-0.5 pl-3.5 text-base italic leading-relaxed text-text-muted"
                >
                    {{ t('patient.appointments.doneToggle.cancelledNotice') }}
                </p>
                <p
                    v-if="appointment.cancellation_reason"
                    class="border-l-[3px] border-danger/40 py-0.5 pl-3.5 text-base leading-relaxed text-text"
                >
                    <span class="font-semibold text-text-heading">
                        {{ t('patient.appointments.cancelDialog.savedReasonLabel') }}
                    </span>
                    {{ appointment.cancellation_reason }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
