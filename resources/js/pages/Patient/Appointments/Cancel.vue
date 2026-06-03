<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Stethoscope } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { useAppointmentDisplay } from '@/Components/Appointments/useAppointmentDisplay';
import PatientPageShell from '@/Components/Patient/PatientPageShell.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import { formatAppointmentAddress } from '@/lib/appointments/formatAppointmentAddress';
import type { PatientAppointmentOutcomePageProps } from '@/lib/patient/appointments/screen/patientAppointmentOutcomePageProps';
import {
    patientAppointmentFormPrimaryPairButtonClass,
    patientSoftDangerActionButtonClass,
} from '@/lib/patient/appointments/ui/patientSoftDangerActionButtonClass';
import { patientPageTitleClass } from '@/lib/patient/patientPageTypography';

const props = defineProps<PatientAppointmentOutcomePageProps>();

const { t } = useI18n();
const { formatDateOnly, formatTimeOnly, doctorTypeLabel } =
    useAppointmentDisplay();

const fieldClass =
    'box-border min-h-34 w-full shrink-0 rounded-2xl border-2 border-border bg-surface px-4 py-3.5 text-lg leading-normal text-text placeholder:text-text-muted focus-visible:border-focus focus-visible:ring-2 focus-visible:ring-focus/25';

const form = useForm({
    cancellation_reason: '',
});

function submit(): void {
    const trimmed = form.cancellation_reason.trim();

    form.transform(() => ({
        status: 'cancelled' as const,
        cancellation_reason: trimmed === '' ? null : trimmed,
    })).patch(
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
                    class="text-primary text-base font-semibold underline-offset-2 hover:underline"
                >
                    {{
                        t(
                            'patient.appointments.outcomePages.backToAppointments',
                        )
                    }}
                </Link>
                <h1 :class="patientPageTitleClass">
                    {{ t('patient.appointments.cancelDialog.title') }}
                </h1>
                <p
                    class="text-text-muted max-w-prose text-base leading-relaxed"
                >
                    {{ t('patient.appointments.cancelDialog.description') }}
                </p>
            </div>

            <Card
                class="border-border/80 bg-surface text-text rounded-2xl border shadow-md shadow-black/[0.04] sm:rounded-3xl"
            >
                <CardContent class="space-y-5 p-5 sm:p-6">
                    <div class="flex items-start gap-4">
                        <div
                            class="bg-primary/12 flex size-12 shrink-0 items-center justify-center rounded-xl"
                            aria-hidden="true"
                        >
                            <Stethoscope class="text-primary size-6" />
                        </div>
                        <div class="min-w-0 flex-1 space-y-1">
                            <p
                                class="text-text-heading text-lg leading-snug font-bold sm:text-xl"
                            >
                                {{
                                    props.appointment.doctor_type
                                        ? doctorTypeLabel(
                                              props.appointment.doctor_type,
                                          )
                                        : props.appointment.provider_name
                                }}
                            </p>
                            <p
                                v-if="props.appointment.doctor_type"
                                class="text-text-muted text-base leading-snug font-normal"
                            >
                                {{ props.appointment.provider_name }}
                            </p>
                        </div>
                    </div>

                    <div class="text-text space-y-4 text-base leading-relaxed">
                        <p>
                            <span class="text-text-heading font-semibold">
                                {{ t('patient.appointments.labels.when') }}:
                            </span>
                            {{ formatDateOnly(props.appointment.starts_at) }}
                        </p>
                        <p>
                            <span class="text-text-heading font-semibold">
                                {{ t('patient.appointments.labels.time') }}:
                            </span>
                            {{ formatTimeOnly(props.appointment.starts_at) }}
                        </p>
                        <p class="text-pretty wrap-break-word">
                            <span class="text-text-heading font-semibold">
                                {{ t('patient.appointments.labels.where') }}:
                            </span>
                            {{ formatAppointmentAddress(props.appointment) }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <form
                class="border-border/80 bg-surface space-y-5 rounded-3xl border-2 p-5 shadow-sm shadow-black/[0.04] sm:p-6"
                @submit.prevent="submit"
            >
                <div class="space-y-2">
                    <p class="text-text-heading text-base font-semibold">
                        {{ t('patient.appointments.cancelDialog.reasonLabel') }}
                    </p>
                    <textarea
                        id="appointment-cancel-reason"
                        v-model="form.cancellation_reason"
                        rows="5"
                        :class="fieldClass"
                        :placeholder="
                            t(
                                'patient.appointments.cancelDialog.reasonPlaceholder',
                            )
                        "
                    />
                    <p
                        v-if="form.errors.cancellation_reason"
                        class="text-danger text-sm font-medium"
                    >
                        {{ form.errors.cancellation_reason }}
                    </p>
                </div>

                <div
                    class="flex w-full min-w-0 flex-col gap-3 sm:flex-row-reverse sm:gap-3"
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
