<script setup lang="ts">
import {
    Calendar,
    Clock,
    MapPin,
    Pencil,
    Stethoscope,
    Trash2,
} from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import AppointmentDoneToggle from '@/Components/Patient/Appointments/AppointmentDoneToggle.vue';
import { useAppointmentDisplay } from '@/Components/Patient/Appointments/useAppointmentDisplay';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import type {
    Appointment,
    AppointmentCancelledCommitPayload,
    AppointmentDoneCommitPayload,
} from '@/lib/types';

defineProps<{
    appointment: Appointment;
    doneDisplayed: boolean;
    isPatching: boolean;
}>();

const emit = defineEmits<{
    edit: [];
    delete: [];
    'update:done': [done: boolean];
    'commit-done': [payload: AppointmentDoneCommitPayload];
    'commit-cancelled': [payload: AppointmentCancelledCommitPayload];
}>();

const { t } = useI18n();
const { formatDateOnly, formatTimeOnly, doctorTypeLabel } =
    useAppointmentDisplay();
</script>

<template>
    <Card
        class="rounded-3xl border border-border/80 bg-surface text-text shadow-md shadow-black/[0.04]"
    >
        <CardContent class="space-y-6 p-6 sm:p-7">
            <div class="flex items-start gap-4">
                <div
                    class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/12"
                    aria-hidden="true"
                >
                    <Stethoscope class="size-6 text-primary" />
                </div>
                <div class="min-w-0 flex-1 space-y-1">
                    <p
                        class="text-lg font-bold leading-snug text-text-heading sm:text-xl"
                    >
                        {{ doctorTypeLabel(appointment.doctor_type) }}
                    </p>
                    <p
                        class="text-base font-normal leading-snug text-text-muted"
                    >
                        {{ appointment.provider_name }}
                    </p>
                </div>
                <div class="-mr-1 -mt-1 flex shrink-0 items-start gap-0.5">
                    <Button
                        v-if="appointment.status !== 'cancelled'"
                        variant="ghost"
                        size="icon"
                        class="text-text-muted hover:bg-surface-hover hover:text-text-heading"
                        :aria-label="t('patient.appointments.actions.edit')"
                        @click="emit('edit')"
                    >
                        <Pencil
                            class="size-5"
                            aria-hidden="true"
                        />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="text-text-muted hover:bg-danger/10 hover:text-danger"
                        :aria-label="t('patient.appointments.actions.delete')"
                        @click="emit('delete')"
                    >
                        <Trash2
                            class="size-5"
                            aria-hidden="true"
                        />
                    </Button>
                </div>
            </div>

            <div class="space-y-5">
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
                            class="text-lg font-medium leading-relaxed text-text break-words text-pretty sm:text-xl sm:leading-snug"
                        >
                            {{ appointment.address }}
                        </p>
                    </div>
                </div>
            </div>

            <p
                v-if="appointment.notes"
                class="border-l-[3px] border-primary py-0.5 pl-3.5 text-base italic leading-relaxed text-text sm:text-lg"
            >
                {{ appointment.notes }}
            </p>

            <AppointmentDoneToggle
                v-if="appointment.status !== 'cancelled'"
                :instance-key="appointment.id"
                :model-value="doneDisplayed"
                :disabled="isPatching"
                @update:model-value="emit('update:done', $event)"
                @commit-done="emit('commit-done', $event)"
                @commit-cancelled="emit('commit-cancelled', $event)"
            />

            <div
                v-else
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
