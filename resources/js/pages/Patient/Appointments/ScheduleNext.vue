<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import {
    patientAppointmentFormPrimaryPairButtonClass,
    patientSoftDangerActionButtonClass,
} from '@/lib/patient/appointments/patientSoftDangerActionButtonClass';
import type { PatientAppointmentScheduleNextPageProps } from '@/lib/patient/appointments/patientAppointmentScheduleNextPageProps';

const props = defineProps<PatientAppointmentScheduleNextPageProps>();

const { t } = useI18n();

function goToOverview(): void {
    router.get(route('patient.appointments'));
}

function goToNewAppointment(): void {
    router.get(route('patient.appointments', { open_create: 1 }));
}

const descriptionKey =
    props.outcome === 'done'
        ? 'patient.appointments.scheduleNext.descriptionDone'
        : 'patient.appointments.scheduleNext.descriptionCancelled';
</script>

<template>
    <Head>
        <title>{{ t('patient.appointments.scheduleNext.title') }}</title>
    </Head>

    <PatientLayout>
        <div class="flex flex-col gap-8">
            <div class="space-y-3">
                <h1 class="text-3xl font-bold leading-tight text-text-heading">
                    {{ t('patient.appointments.scheduleNext.title') }}
                </h1>
                <p class="max-w-prose text-base leading-relaxed text-text-muted">
                    {{ t(descriptionKey) }}
                </p>
            </div>

            <div
                class="flex min-w-0 w-full flex-col gap-3 rounded-3xl border-2 border-border/80 bg-surface p-5 shadow-sm shadow-black/[0.04] sm:flex-row-reverse sm:gap-3 sm:p-6"
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
        </div>
    </PatientLayout>
</template>
