<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Stethoscope } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { useAppointmentDisplay } from '@/Components/Appointments/useAppointmentDisplay';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { patientPageTitleClass } from '@/lib/patient/patientPageTypography';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import type { PatientAppointmentOutcomePageProps } from '@/lib/patient/appointments/screen/patientAppointmentOutcomePageProps';
import {
    patientAppointmentFormPrimaryPairButtonClass,
    patientSoftDangerActionButtonClass,
} from '@/lib/patient/appointments/ui/patientSoftDangerActionButtonClass';

const props = defineProps<PatientAppointmentOutcomePageProps>();

const { t } = useI18n();
const { formatDateOnly, formatTimeOnly, doctorTypeLabel } = useAppointmentDisplay();

const fieldClass =
    'box-border min-h-34 w-full shrink-0 rounded-2xl border-2 border-border bg-surface px-4 py-3.5 text-lg leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25';

const form = useForm({
    cancellation_reason: '',
});

function submit(): void {
    const trimmed = form.cancellation_reason.trim();

    form
        .transform(() => ({
            status: 'cancelled' as const,
            cancellation_reason: trimmed === '' ? null : trimmed,
        }))
        .patch(
            `${route('patient.appointments.update', props.appointment.id)}?outcome_follow_up=cancelled`,
        );
}
</script>

<template>
    <Head>
        <title>{{ t('patient.appointments.cancelDialog.title') }}</title>
    </Head>

    <PatientLayout>
        <PatientPageShell :title="t('patient.appointments.cancelDialog.title')">
            <div class="space-y-3">
                <Link
                    :href="route('patient.appointments')"
                    class="text-base font-semibold text-primary underline-offset-2 hover:underline"
                >
                    {{ t('patient.appointments.outcomePages.backToAppointments') }}
                </Link>
                <h1 :class="patientPageTitleClass">
                    {{ t('patient.appointments.cancelDialog.title') }}
                </h1>
                <p class="max-w-prose text-base leading-relaxed text-text-muted">
                    {{ t('patient.appointments.cancelDialog.description') }}
                </p>
            </div>

            <Card
                class="rounded-2xl border border-border/80 bg-surface text-text shadow-md shadow-black/[0.04] sm:rounded-3xl"
            >
                <CardContent class="space-y-5 p-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/12"
                            aria-hidden="true"
                        >
                            <Stethoscope class="size-6 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <p class="text-lg font-bold leading-snug text-text-heading sm:text-xl">
                                {{
                                    props.appointment.doctor_type
                                        ? doctorTypeLabel(props.appointment.doctor_type)
                                        : props.appointment.provider_name
                                }}
                            </p>
                            <p
                                v-if="props.appointment.doctor_type"
                                class="text-base font-normal leading-snug text-text-muted"
                            >
                                {{ props.appointment.provider_name }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4 text-base leading-relaxed text-text">
                        <p>
                            <span class="font-semibold text-text-heading">
                                {{ t('patient.appointments.labels.when') }}:
                            </span>
                            {{ formatDateOnly(props.appointment.starts_at) }}
                        </p>
                        <p>
                            <span class="font-semibold text-text-heading">
                                {{ t('patient.appointments.labels.time') }}:
                            </span>
                            {{ formatTimeOnly(props.appointment.starts_at) }}
                        </p>
                        <p class="wrap-break-word text-pretty">
                            <span class="font-semibold text-text-heading">
                                {{ t('patient.appointments.labels.where') }}:
                            </span>
                            {{ formatAppointmentAddress(props.appointment) }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <form
                class="space-y-5 rounded-3xl border-2 border-border/80 bg-surface p-5 shadow-sm shadow-black/[0.04] sm:p-6"
                @submit.prevent="submit"
            >
                <div class="space-y-2">
                    <p class="text-base font-semibold text-text-heading">
                        {{ t('patient.appointments.cancelDialog.reasonLabel') }}
                    </p>
                    <textarea
                        id="appointment-cancel-reason"
                        v-model="form.cancellation_reason"
                        rows="5"
                        :class="fieldClass"
                        :placeholder="t('patient.appointments.cancelDialog.reasonPlaceholder')"
                    />
                    <p
                        v-if="form.errors.cancellation_reason"
                        class="text-sm font-medium text-danger"
                    >
                        {{ form.errors.cancellation_reason }}
                    </p>
                </div>

                <div
                    class="flex min-w-0 w-full flex-col gap-3 sm:flex-row-reverse sm:gap-3"
                >
                    <Button
                        type="submit"
                        variant="default"
                        size="lg"
                        :class="patientAppointmentFormPrimaryPairButtonClass"
                        :disabled="form.processing"
                    >
                        {{ t('patient.appointments.cancelDialog.confirm') }}
                    </Button>
                    <Button
                        type="button"
                        variant="secondary"
                        size="lg"
                        :class="patientSoftDangerActionButtonClass"
                        @click="router.get(route('patient.appointments'))"
                    >
                        {{ t('patient.appointments.actions.cancel') }}
                    </Button>
                </div>
            </form>
        </PatientPageShell>
    </PatientLayout>
</template>
