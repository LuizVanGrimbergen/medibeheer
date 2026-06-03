<script setup lang="ts">
import {
    Calendar,
    Car,
    CheckCircle2,
    CircleX,
    Clock,
    MapPin,
    Stethoscope,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppointmentDoneToggle from '@/Components/Appointments/AppointmentDoneToggle.vue';
import { useAppointmentDisplay } from '@/Components/Appointments/useAppointmentDisplay';
import PatientListCardActionsToolbar from '@/Components/Patient/PatientListCardActionsToolbar.vue';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Collapsible, CollapsibleContent } from '@/Components/ui/collapsible';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import {
    patientPageCardHeaderSummaryClass,
    patientPageCardHeaderWithActionsClass,
} from '@/lib/patient/patientPageTypography';
import type {
    AppointmentStatusValue,
    AppointmentTransportStatusValue,
} from '@/lib/types';
import { cn } from '@/lib/utils';

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

const props = withDefaults(
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
        anchorId?: string;
        defaultOpen?: boolean;
    }>(),
    {
        defaultOpen: false,
    },
);

const isOpen = ref(props.defaultOpen);

const showActionsToolbar = computed(() => props.showActions ?? true);

const canEditAppointment = computed(
    () => showActionsToolbar.value && props.appointment.status !== 'cancelled',
);

const canDeleteAppointment = computed(() => showActionsToolbar.value);

const emit = defineEmits<{
    edit: [];
    delete: [];
    'update:done': [done: boolean];
}>();

const { t } = useI18n();
const { formatDateOnly, formatTimeOnly, doctorTypeLabel } =
    useAppointmentDisplay();

const headerSummary = computed(
    () =>
        `${formatDateOnly(props.appointment.starts_at)} · ${formatTimeOnly(props.appointment.starts_at)}`,
);

const showAppointmentDoneToggle = computed(
    () =>
        (props.showDoneToggle ?? true) &&
        props.appointment.status !== 'cancelled' &&
        props.completeFormHref !== undefined &&
        props.cancelFormHref !== undefined,
);
</script>

<template>
    <Card
        :id="props.anchorId"
        :class="
            cn(
                'border-border/80 bg-surface text-text w-full min-w-0 scroll-mt-6 rounded-3xl border shadow-md shadow-black/[0.04]',
            )
        "
    >
        <CardContent class="relative p-6 sm:p-7">
            <Collapsible v-model:open="isOpen">
                <PatientListCardActionsToolbar
                    v-if="showActionsToolbar"
                    :ariaLabel="t('patient.appointments.cardActionsAriaLabel')"
                    :showEdit="canEditAppointment"
                    :showDelete="canDeleteAppointment"
                    :editAriaLabel="t('patient.appointments.actions.edit')"
                    :deleteAriaLabel="t('patient.appointments.actions.delete')"
                    @edit="emit('edit')"
                    @delete="emit('delete')"
                />

                <div
                    class="flex min-w-0 items-start gap-4"
                    :class="
                        showActionsToolbar
                            ? patientPageCardHeaderWithActionsClass
                            : null
                    "
                >
                    <div
                        class="bg-primary/12 flex size-12 shrink-0 items-center justify-center rounded-xl"
                        aria-hidden="true"
                    >
                        <Stethoscope class="text-primary size-6" />
                    </div>
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <p
                            class="text-text-heading text-lg leading-snug font-bold sm:text-xl"
                        >
                            {{
                                appointment.doctor_type
                                    ? doctorTypeLabel(appointment.doctor_type)
                                    : appointment.provider_name
                            }}
                        </p>
                        <p
                            v-if="appointment.doctor_type"
                            class="text-text-heading text-base leading-snug font-medium sm:text-lg"
                        >
                            {{ appointment.provider_name }}
                        </p>
                        <p
                            v-if="!isOpen"
                            :class="patientPageCardHeaderSummaryClass"
                        >
                            {{ headerSummary }}
                        </p>
                    </div>
                </div>

                <AppointmentDoneToggle
                    v-if="showAppointmentDoneToggle"
                    class="mt-5"
                    :model-value="doneDisplayed"
                    :disabled="isPatching"
                    :complete-form-href="completeFormHref!"
                    :cancel-form-href="cancelFormHref!"
                    @update:model-value="emit('update:done', $event)"
                />

                <CollapsibleContent>
                    <div class="space-y-6 pt-4">
                        <div class="space-y-5">
                            <div
                                v-if="
                                    appointment.status === 'done' ||
                                    appointment.status === 'cancelled'
                                "
                                class="flex gap-4 sm:gap-5"
                            >
                                <CheckCircle2
                                    v-if="appointment.status === 'done'"
                                    class="text-primary mt-0.5 size-6 shrink-0"
                                    :stroke-width="2"
                                    aria-hidden="true"
                                />
                                <CircleX
                                    v-else
                                    class="text-primary mt-0.5 size-6 shrink-0"
                                    :stroke-width="2"
                                    aria-hidden="true"
                                />
                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <p
                                        class="text-text-heading text-base leading-tight font-semibold"
                                    >
                                        {{
                                            t(
                                                'patient.appointments.fields.status',
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="text-lg leading-relaxed font-medium tracking-tight sm:text-xl sm:leading-snug"
                                        :class="
                                            appointment.status === 'done'
                                                ? 'text-success'
                                                : 'text-danger'
                                        "
                                    >
                                        {{
                                            appointment.status === 'done'
                                                ? t(
                                                      'patient.appointments.statuses.done',
                                                  )
                                                : t(
                                                      'patient.appointments.statuses.cancelled',
                                                  )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-4 sm:gap-5">
                                <Calendar
                                    class="text-primary mt-0.5 size-6 shrink-0"
                                    :stroke-width="2"
                                    aria-hidden="true"
                                />
                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <p
                                        class="text-text-heading text-base leading-tight font-semibold"
                                    >
                                        {{
                                            t(
                                                'patient.appointments.labels.when',
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="text-text text-lg leading-relaxed font-medium tracking-tight sm:text-xl sm:leading-snug"
                                    >
                                        {{
                                            formatDateOnly(
                                                appointment.starts_at,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-4 sm:gap-5">
                                <Clock
                                    class="text-primary mt-0.5 size-6 shrink-0"
                                    :stroke-width="2"
                                    aria-hidden="true"
                                />
                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <p
                                        class="text-text-heading text-base leading-tight font-semibold"
                                    >
                                        {{
                                            t(
                                                'patient.appointments.labels.time',
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="text-text text-lg leading-relaxed font-medium sm:text-xl sm:leading-snug"
                                    >
                                        {{
                                            formatTimeOnly(
                                                appointment.starts_at,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-4 sm:gap-5">
                                <MapPin
                                    class="text-primary mt-1 size-6 shrink-0 self-start"
                                    :stroke-width="2"
                                    aria-hidden="true"
                                />
                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <p
                                        class="text-text-heading text-base leading-tight font-semibold"
                                    >
                                        {{
                                            t(
                                                'patient.appointments.labels.where',
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="text-text text-lg leading-relaxed font-medium text-pretty wrap-break-word sm:text-xl sm:leading-snug"
                                    >
                                        {{
                                            formatAppointmentAddress(
                                                appointment,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="showTransportSection ?? true"
                                class="flex gap-4 sm:gap-5"
                            >
                                <Car
                                    class="text-primary mt-1 size-6 shrink-0 self-start"
                                    :stroke-width="2"
                                    aria-hidden="true"
                                />
                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <p
                                        class="text-text-heading text-base leading-tight font-semibold"
                                    >
                                        {{
                                            t(
                                                'patient.appointments.labels.transport',
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="text-lg leading-relaxed font-medium sm:text-xl sm:leading-snug"
                                        :class="
                                            appointment.needs_transport
                                                ? appointment.transport_status ===
                                                  'accepted'
                                                    ? 'text-text'
                                                    : appointment.transport_status ===
                                                        'declined'
                                                      ? 'text-danger'
                                                      : 'text-text-muted'
                                                : 'text-text-muted'
                                        "
                                    >
                                        {{
                                            appointment.needs_transport
                                                ? appointment.transport_status ===
                                                  'accepted'
                                                    ? t(
                                                          'patient.appointments.transport.acceptedBy',
                                                          {
                                                              name:
                                                                  appointment
                                                                      .transport_family
                                                                      ?.name ??
                                                                  '',
                                                          },
                                                      )
                                                    : appointment.transport_status ===
                                                        'declined'
                                                      ? t(
                                                            'patient.appointments.transport.declined',
                                                        )
                                                      : t(
                                                            'patient.appointments.transport.requested',
                                                        )
                                                : t(
                                                      'patient.appointments.transport.notNeeded',
                                                  )
                                        }}
                                    </p>
                                    <slot name="transport-actions" />
                                </div>
                            </div>
                        </div>

                        <p
                            v-if="appointment.notes"
                            class="border-primary text-text border-l-[3px] py-0.5 pl-3.5 text-base leading-relaxed italic sm:text-lg"
                        >
                            {{ appointment.notes }}
                        </p>

                        <div
                            v-if="
                                appointment.status === 'done' &&
                                appointment.doctor_visit_summary
                            "
                            class="border-success/50 space-y-2 border-l-[3px] py-0.5 pl-3.5"
                        >
                            <p
                                class="text-text-heading text-base leading-snug font-semibold"
                            >
                                {{
                                    doneSummaryLabel ??
                                    t('patient.appointments.labels.afterVisit')
                                }}
                            </p>
                            <p
                                class="text-text text-base leading-relaxed sm:text-lg"
                            >
                                {{ appointment.doctor_visit_summary }}
                            </p>
                        </div>

                        <div
                            v-if="appointment.status === 'cancelled'"
                            class="border-border/70 space-y-3 border-t pt-5"
                        >
                            <p
                                class="border-text-muted/35 text-text-muted border-l-[3px] py-0.5 pl-3.5 text-base leading-relaxed italic"
                            >
                                {{
                                    t(
                                        'patient.appointments.doneToggle.cancelledNotice',
                                    )
                                }}
                            </p>
                            <p
                                v-if="appointment.cancellation_reason"
                                class="border-danger/40 text-text border-l-[3px] py-0.5 pl-3.5 text-base leading-relaxed"
                            >
                                <span class="text-text-heading font-semibold">
                                    {{
                                        t(
                                            'patient.appointments.cancelDialog.savedReasonLabel',
                                        )
                                    }}
                                </span>
                                {{ appointment.cancellation_reason }}
                            </p>
                        </div>
                    </div>
                </CollapsibleContent>

                <PatientListCardDetailsToggle
                    :mode="isOpen ? 'collapse' : 'expand'"
                    :label="
                        t(
                            isOpen
                                ? 'patient.appointments.cardCollapseHint'
                                : 'patient.appointments.cardExpandHint',
                        )
                    "
                    :ariaLabel="
                        t(
                            isOpen
                                ? 'patient.appointments.hideDetails'
                                : 'patient.appointments.showDetails',
                        )
                    "
                />
            </Collapsible>
        </CardContent>
    </Card>
</template>
