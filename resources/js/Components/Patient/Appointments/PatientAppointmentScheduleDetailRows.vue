<script setup lang="ts">
import { Calendar, Clock, MapPin } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAppointmentDisplay } from '@/Components/Appointments/useAppointmentDisplay';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import type { Appointment } from '@/lib/types';

const props = defineProps<{
    appointment: Pick<
        Appointment,
        'starts_at' | 'street' | 'house_number' | 'postal_code' | 'city'
    >;
}>();

const { t } = useI18n();
const { formatDateOnly, formatTimeOnly } = useAppointmentDisplay();

const formattedAddress = computed(() =>
    formatAppointmentAddress(props.appointment),
);
</script>

<template>
    <div class="space-y-5">
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
                    {{ t('patient.appointments.labels.when') }}
                </p>
                <p
                    class="text-text text-lg leading-relaxed font-medium tracking-tight sm:text-xl sm:leading-snug"
                >
                    {{ formatDateOnly(appointment.starts_at) }}
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
                    {{ t('patient.appointments.labels.time') }}
                </p>
                <p
                    class="text-text text-lg leading-relaxed font-medium sm:text-xl sm:leading-snug"
                >
                    {{ formatTimeOnly(appointment.starts_at) }}
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
                    {{ t('patient.appointments.labels.where') }}
                </p>
                <p
                    class="text-text text-lg leading-relaxed font-medium text-pretty wrap-break-word sm:text-xl sm:leading-snug"
                >
                    {{ formattedAddress }}
                </p>
            </div>
        </div>
    </div>
</template>
