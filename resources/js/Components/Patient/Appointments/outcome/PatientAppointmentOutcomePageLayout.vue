<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PatientAppointmentScheduleNextSuccessScreen from '@/Components/Patient/Appointments/PatientAppointmentScheduleNextSuccessScreen.vue';
import PatientAppointmentOutcomeSummaryCard from '@/Components/Patient/Appointments/outcome/PatientAppointmentOutcomeSummaryCard.vue';
import PatientShellPageWizard from '@/Components/Patient/form/PatientShellPageWizard.vue';
import PatientShellWizardScrollBody from '@/Components/Patient/form/PatientShellWizardScrollBody.vue';
import { Button } from '@/Components/ui/button';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import {
    patientFormWizardFooterCancelButtonClass,
    patientFormWizardFooterPrimaryButtonClass,
    patientFormWizardFooterRowClass,
    patientShellPageFillClass,
    patientShellWizardFormClass,
    patientShellWizardStepPanelClass,
} from '@/lib/patient/patientShellDialogLayout';
import type { Appointment } from '@/lib/types';

const props = defineProps<{
    title: string;
    formId: string;
    outcome: 'done' | 'cancelled';
    appointment: Appointment;
    processing: boolean;
    primaryLabel: string;
}>();

const scheduleNextOpen = defineModel<boolean>('scheduleNextOpen', {
    required: true,
});

const emit = defineEmits<{
    submit: [];
}>();

const { t } = useI18n();
</script>

<template>
    <PatientLayout>
        <PatientAppointmentScheduleNextSuccessScreen
            v-model:open="scheduleNextOpen"
            :outcome="props.outcome"
        />

        <div :class="patientShellPageFillClass">
            <PatientShellPageWizard :title="props.title">
                <form
                    :id="props.formId"
                    :class="patientShellWizardFormClass"
                    novalidate
                    @submit.prevent="emit('submit')"
                >
                    <PatientShellWizardScrollBody :active="true">
                        <div :class="patientShellWizardStepPanelClass">
                            <PatientAppointmentOutcomeSummaryCard
                                :appointment="props.appointment"
                            />

                            <slot />
                        </div>

                        <template #footer>
                            <div :class="patientFormWizardFooterRowClass">
                                <Button
                                    type="submit"
                                    variant="default"
                                    size="lg"
                                    :class="
                                        patientFormWizardFooterPrimaryButtonClass
                                    "
                                    :disabled="props.processing"
                                >
                                    {{ props.primaryLabel }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    size="lg"
                                    :class="
                                        patientFormWizardFooterCancelButtonClass
                                    "
                                    :disabled="props.processing"
                                    @click="
                                        router.get(
                                            route('patient.appointments'),
                                        )
                                    "
                                >
                                    {{
                                        t(
                                            'patient.appointments.actions.cancel',
                                        )
                                    }}
                                </Button>
                            </div>
                        </template>
                    </PatientShellWizardScrollBody>
                </form>
            </PatientShellPageWizard>
        </div>
    </PatientLayout>
</template>
