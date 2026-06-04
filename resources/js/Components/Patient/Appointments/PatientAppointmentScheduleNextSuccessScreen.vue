<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import { Button } from '@/Components/ui/button';
import {
    patientAppointmentFormPrimaryPairButtonClass,
    patientSoftDangerActionButtonClass,
} from '@/lib/patient/appointments/ui/patientSoftDangerActionButtonClass';

const open = defineModel<boolean>('open', { required: true });

const props = defineProps<{
    outcome: 'done' | 'cancelled';
}>();

const { t } = useI18n();

const descriptionKey = computed((): string =>
    props.outcome === 'done'
        ? 'patient.appointments.scheduleNext.descriptionDone'
        : 'patient.appointments.scheduleNext.descriptionCancelled',
);

function goToOverview(): void {
    open.value = false;
    router.get(route('patient.appointments'));
}

function goToNewAppointment(): void {
    open.value = false;
    router.get(route('patient.appointments', { open_create: 1 }));
}
</script>

<template>
    <PatientActionSuccessScreen
        v-model:open="open"
        :title="t('patient.appointments.scheduleNext.title')"
        :subtitle="t(descriptionKey)"
    >
        <template #footer>
            <div
                class="flex w-full min-w-0 flex-col gap-3 sm:flex-row-reverse sm:gap-3"
            >
                <Button
                    type="button"
                    variant="default"
                    size="lg"
                    :class="patientAppointmentFormPrimaryPairButtonClass"
                    @click="goToNewAppointment"
                >
                    {{ t('patient.appointments.scheduleNext.yes') }}
                </Button>
                <Button
                    type="button"
                    variant="secondary"
                    size="lg"
                    :class="patientSoftDangerActionButtonClass"
                    @click="goToOverview"
                >
                    {{ t('patient.appointments.scheduleNext.no') }}
                </Button>
            </div>
        </template>
    </PatientActionSuccessScreen>
</template>
