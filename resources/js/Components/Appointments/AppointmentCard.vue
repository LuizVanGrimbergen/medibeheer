<script setup lang="ts">
import {
    ArrowUpRight,
    Car,
    CheckCircle2,
    ChevronDown,
    CircleX,
    Stethoscope,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppointmentDoneToggle from '@/Components/Appointments/AppointmentDoneToggle.vue';
import AppointmentGoogleMapsIconLink from '@/Components/Appointments/AppointmentGoogleMapsIconLink.vue';
import { useAppointmentDisplay } from '@/Components/Appointments/useAppointmentDisplay';
import PatientAppointmentScheduleDetailRows from '@/Components/Patient/Appointments/PatientAppointmentScheduleDetailRows.vue';
import PatientListCardActionsToolbar from '@/Components/Patient/PatientListCardActionsToolbar.vue';
import PatientListCardDetailsToggle from '@/Components/Patient/PatientListCardDetailsToggle.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/Components/ui/collapsible';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import { googleMapsSearchUrlForAppointmentAddress } from '@/lib/google-maps/googleMapsSearchUrlForAppointmentAddress';
import {
    patientPageCardDetailsButtonClass,
    patientPageCardDetailsChevronClass,
    patientPageCardDetailsExpandWrapperClass,
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
        showProviderSubtitle?: boolean;
        transportLabel?: string;
        showCompactGoogleMapsLink?: boolean;
        detailsToggleVariant?: 'footer-button' | 'header';
    }>(),
    {
        defaultOpen: false,
        showProviderSubtitle: true,
        showCompactGoogleMapsLink: false,
        detailsToggleVariant: 'footer-button',
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

const formattedAppointmentAddress = computed(() =>
    formatAppointmentAddress(props.appointment),
);

const googleMapsUrl = computed(() =>
    googleMapsSearchUrlForAppointmentAddress(props.appointment),
);

const usesHeaderDetailsToggle = computed(
    () => props.detailsToggleVariant === 'header',
);

const detailsToggleAriaLabel = computed(() =>
    t(
        isOpen.value
            ? 'patient.appointments.hideDetails'
            : 'patient.appointments.showDetails',
    ),
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
                    class="flex min-w-0 gap-2 sm:gap-3"
                    :class="
                        cn(
                            showCompactGoogleMapsLink || usesHeaderDetailsToggle
                                ? 'items-center'
                                : 'items-start',
                            showActionsToolbar
                                ? patientPageCardHeaderWithActionsClass
                                : null,
                        )
                    "
                >
                    <CollapsibleTrigger
                        v-if="usesHeaderDetailsToggle"
                        as-child
                        class="min-w-0 flex-1"
                    >
                        <div
                            class="hover:bg-surface-2 -mx-2 flex w-full min-w-0 cursor-pointer items-center gap-3 rounded-xl px-2 py-1 text-left transition sm:-mx-3 sm:gap-4 sm:px-3"
                            :aria-label="detailsToggleAriaLabel"
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
                                            ? doctorTypeLabel(
                                                  appointment.doctor_type,
                                              )
                                            : appointment.provider_name
                                    }}
                                </p>
                                <p
                                    v-if="
                                        showProviderSubtitle &&
                                        appointment.doctor_type
                                    "
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
                            <span
                                v-if="
                                    googleMapsUrl !== null &&
                                    showCompactGoogleMapsLink
                                "
                                class="shrink-0"
                                @click.stop
                                @pointerdown.stop
                            >
                                <AppointmentGoogleMapsIconLink
                                    :href="googleMapsUrl"
                                    icon="map-pin"
                                    stop-propagation
                                    :title="
                                        t(
                                            'patient.appointments.labels.openInGoogleMaps',
                                        )
                                    "
                                    :ariaLabel="
                                        t(
                                            'patient.appointments.labels.openInGoogleMapsAria',
                                            {
                                                address:
                                                    formattedAppointmentAddress,
                                            },
                                        )
                                    "
                                />
                            </span>
                            <ChevronDown
                                :size="20"
                                :stroke-width="1.75"
                                :class="
                                    cn(
                                        'text-text-muted shrink-0 transition-transform duration-200',
                                        isOpen && 'rotate-180',
                                    )
                                "
                                aria-hidden="true"
                            />
                        </div>
                    </CollapsibleTrigger>

                    <template v-else>
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
                                        ? doctorTypeLabel(
                                              appointment.doctor_type,
                                          )
                                        : appointment.provider_name
                                }}
                            </p>
                            <p
                                v-if="
                                    showProviderSubtitle &&
                                    appointment.doctor_type
                                "
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
                    </template>

                    <AppointmentGoogleMapsIconLink
                        v-if="
                            googleMapsUrl !== null &&
                            showCompactGoogleMapsLink &&
                            !usesHeaderDetailsToggle
                        "
                        :href="googleMapsUrl"
                        icon="map-pin"
                        stop-propagation
                        :title="
                            t('patient.appointments.labels.openInGoogleMaps')
                        "
                        :ariaLabel="
                            t(
                                'patient.appointments.labels.openInGoogleMapsAria',
                                {
                                    address: formattedAppointmentAddress,
                                },
                            )
                        "
                    />
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

                <div
                    v-if="googleMapsUrl !== null && !showCompactGoogleMapsLink"
                    :class="
                        cn(
                            patientPageCardDetailsExpandWrapperClass,
                            !showAppointmentDoneToggle && 'mt-5',
                        )
                    "
                >
                    <Button
                        as-child
                        variant="outline"
                        :class="patientPageCardDetailsButtonClass"
                    >
                        <a
                            :href="googleMapsUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            :aria-label="
                                t(
                                    'patient.appointments.labels.openInGoogleMapsAria',
                                    {
                                        address: formattedAppointmentAddress,
                                    },
                                )
                            "
                            @click.stop
                        >
                            <MapPin
                                :class="
                                    cn(
                                        patientPageCardDetailsChevronClass,
                                        'text-primary',
                                    )
                                "
                                aria-hidden="true"
                            />
                            <span class="min-w-0">
                                {{
                                    t(
                                        'patient.appointments.labels.openInGoogleMaps',
                                    )
                                }}
                            </span>
                            <ArrowUpRight
                                :class="
                                    cn(
                                        patientPageCardDetailsChevronClass,
                                        'text-primary',
                                    )
                                "
                                aria-hidden="true"
                            />
                        </a>
                    </Button>
                </div>

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
                            <PatientAppointmentScheduleDetailRows
                                :appointment="appointment"
                            />
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
                                            props.transportLabel ??
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
                    v-if="!usesHeaderDetailsToggle"
                    :mode="isOpen ? 'collapse' : 'expand'"
                    :label="
                        t(
                            isOpen
                                ? 'patient.appointments.cardCollapseHint'
                                : 'patient.appointments.cardExpandHint',
                        )
                    "
                    :ariaLabel="detailsToggleAriaLabel"
                />
            </Collapsible>
        </CardContent>
    </Card>
</template>
