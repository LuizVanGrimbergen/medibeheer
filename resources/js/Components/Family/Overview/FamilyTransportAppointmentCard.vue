<script setup lang="ts">
import { Calendar, Clock, MapPin } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AppointmentGoogleMapsIconLink from '@/Components/shared/appointments/AppointmentGoogleMapsIconLink.vue';
import AppointmentPairActionButtons from '@/Components/shared/appointments/AppointmentPairActionButtons.vue';
import { useAppointmentDisplay } from '@/Components/shared/appointments/useAppointmentDisplay';
import { Card, CardContent } from '@/Components/ui/card';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import type { FamilyAcceptedTransportAppointment } from '@/lib/family/overview/familyAcceptedTransportAppointments';
import { googleMapsDirectionsUrlForAppointmentAddress } from '@/lib/google-maps/googleMapsSearchUrlForAppointmentAddress';
import { cn } from '@/lib/utils';

const props = defineProps<{
    appointment: FamilyAcceptedTransportAppointment;
    variant: 'pending' | 'accepted';
    acceptUrl?: string;
    declineUrl?: string;
}>();

const emit = defineEmits<{
    'accept-transport': [];
    'decline-transport': [];
}>();

const { t } = useI18n();

const showTransportActions = computed(
    () =>
        props.variant === 'pending' &&
        props.acceptUrl !== undefined &&
        props.acceptUrl !== '',
);

const { formatDateOnly, formatTimeOnly } = useAppointmentDisplay();

const accentIconClass =
    props.variant === 'pending'
        ? 'text-stock-near dark:text-stock-near-dark'
        : 'text-primary';

const cardBorderClass =
    props.variant === 'pending'
        ? 'border-stock-near/70 dark:border-stock-near-dark/75'
        : 'border-primary/50';

const formattedAddress = computed(() =>
    formatAppointmentAddress(props.appointment),
);

const routeUrl = computed(() =>
    googleMapsDirectionsUrlForAppointmentAddress(props.appointment),
);
</script>

<template>
    <div class="block w-full">
        <Card
            :class="
                cn(
                    'bg-surface shadow-sm transition',
                    'rounded-2xl',
                    cardBorderClass,
                )
            "
        >
            <CardContent class="p-4 md:p-4">
                <div class="flex items-center gap-3">
                    <div class="min-w-0 flex-1 space-y-2.5">
                        <p
                            class="text-text-heading text-base leading-snug font-semibold md:text-[0.9375rem]"
                        >
                            {{ props.appointment.patient_name }}
                        </p>

                        <div
                            class="text-text flex flex-wrap items-center gap-x-5 gap-y-2 text-base"
                        >
                            <span
                                class="inline-flex min-w-0 items-center gap-2"
                            >
                                <Calendar
                                    :size="18"
                                    :class="cn('shrink-0', accentIconClass)"
                                    aria-hidden="true"
                                />
                                <span class="font-semibold">
                                    {{
                                        formatDateOnly(
                                            props.appointment.starts_at,
                                        )
                                    }}
                                </span>
                            </span>
                            <span
                                class="inline-flex min-w-0 items-center gap-2"
                            >
                                <Clock
                                    :size="18"
                                    :class="cn('shrink-0', accentIconClass)"
                                    aria-hidden="true"
                                />
                                <span class="font-semibold tabular-nums">
                                    {{
                                        formatTimeOnly(
                                            props.appointment.starts_at,
                                        )
                                    }}
                                </span>
                            </span>
                        </div>

                        <div
                            v-if="formattedAddress !== ''"
                            class="text-text inline-flex min-w-0 items-start gap-2 text-base"
                        >
                            <MapPin
                                :size="18"
                                :class="cn('mt-0.5 shrink-0', accentIconClass)"
                                aria-hidden="true"
                            />
                            <span
                                class="min-w-0 leading-snug font-semibold text-pretty wrap-break-word"
                            >
                                {{ formattedAddress }}
                            </span>
                        </div>
                    </div>

                    <AppointmentGoogleMapsIconLink
                        v-if="routeUrl"
                        :href="routeUrl"
                        icon="route"
                        :title="t('family.overview.transportOpenRoute')"
                        :ariaLabel="
                            t('family.overview.transportOpenRouteAria', {
                                address: formattedAddress,
                            })
                        "
                    />
                </div>
            </CardContent>

            <div
                v-if="showTransportActions"
                class="border-border border-t px-4 pt-3 pb-4 md:px-5 md:pb-5"
            >
                <AppointmentPairActionButtons
                    centered
                    :show-secondary="Boolean(props.declineUrl)"
                    @primary-click="emit('accept-transport')"
                    @secondary-click="emit('decline-transport')"
                >
                    <template #primary>
                        {{ t('family.appointments.acceptTransport') }}
                    </template>
                    <template #secondary>
                        {{ t('family.appointments.declineTransport') }}
                    </template>
                </AppointmentPairActionButtons>
            </div>
        </Card>
    </div>
</template>
