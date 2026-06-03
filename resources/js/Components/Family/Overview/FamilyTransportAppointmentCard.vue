<script setup lang="ts">
import { Calendar, ChevronRight, Clock, MapPin } from 'lucide-vue-next';
import { useAppointmentDisplay } from '@/Components/Appointments/useAppointmentDisplay';
import { Card, CardContent } from '@/Components/ui/card';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import type { FamilyAcceptedTransportAppointment } from '@/lib/family/overview/familyAcceptedTransportAppointments';
import { cn } from '@/lib/utils';

const props = defineProps<{
    appointment: FamilyAcceptedTransportAppointment;
    variant: 'pending' | 'accepted';
}>();

const emit = defineEmits<{
    click: [];
}>();

const { formatDateOnly, formatTimeOnly, doctorTypeLabel } =
    useAppointmentDisplay();

const accentIconClass =
    props.variant === 'pending'
        ? 'text-stock-near dark:text-stock-near-dark'
        : 'text-primary';

const cardBorderClass =
    props.variant === 'pending'
        ? 'border-stock-near/70 dark:border-stock-near-dark/75'
        : 'border-primary/50';

function appointmentTitle(
    appointment: FamilyAcceptedTransportAppointment,
): string {
    const provider = appointment.provider_name.trim();

    if (provider !== '') {
        return provider;
    }

    return doctorTypeLabel(appointment.doctor_type);
}

function appointmentAddress(
    appointment: FamilyAcceptedTransportAppointment,
): string {
    return formatAppointmentAddress(appointment);
}
</script>

<template>
    <button
        type="button"
        class="group block w-full text-left"
        @click="emit('click')"
    >
        <Card
            :class="
                cn(
                    'bg-surface group-hover:bg-surface-2 shadow-sm transition',
                    'rounded-2xl',
                    cardBorderClass,
                )
            "
        >
            <CardContent class="p-4 md:p-4">
                <div class="flex items-start gap-3">
                    <div class="min-w-0 flex-1 space-y-2.5">
                        <div class="space-y-0.5">
                            <p
                                class="text-text-heading text-base leading-snug font-semibold md:text-[0.9375rem]"
                            >
                                {{ appointmentTitle(props.appointment) }}
                            </p>
                            <p class="text-text text-sm font-medium">
                                {{ props.appointment.patient_name }}
                            </p>
                        </div>

                        <div
                            class="text-text flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm"
                        >
                            <span
                                class="inline-flex min-w-0 items-center gap-1.5"
                            >
                                <Calendar
                                    :size="16"
                                    :class="cn('shrink-0', accentIconClass)"
                                    aria-hidden="true"
                                />
                                <span class="font-medium">
                                    {{
                                        formatDateOnly(
                                            props.appointment.starts_at,
                                        )
                                    }}
                                </span>
                            </span>
                            <span
                                class="inline-flex min-w-0 items-center gap-1.5"
                            >
                                <Clock
                                    :size="16"
                                    :class="cn('shrink-0', accentIconClass)"
                                    aria-hidden="true"
                                />
                                <span class="font-medium tabular-nums">
                                    {{
                                        formatTimeOnly(
                                            props.appointment.starts_at,
                                        )
                                    }}
                                </span>
                            </span>
                        </div>

                        <p
                            v-if="appointmentAddress(props.appointment) !== ''"
                            class="text-text-muted flex items-start gap-1.5 text-sm leading-snug"
                        >
                            <MapPin
                                :size="16"
                                :class="cn('mt-0.5 shrink-0', accentIconClass)"
                                aria-hidden="true"
                            />
                            <span class="min-w-0 text-pretty wrap-break-word">
                                {{ appointmentAddress(props.appointment) }}
                            </span>
                        </p>
                    </div>

                    <ChevronRight
                        :size="18"
                        class="text-text-muted group-hover:text-text mt-0.5 shrink-0 transition"
                        aria-hidden="true"
                    />
                </div>
            </CardContent>
        </Card>
    </button>
</template>
