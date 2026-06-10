<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PatientAppointmentOutcomeSummaryCard from '@/Components/Patient/Appointments/outcome/PatientAppointmentOutcomeSummaryCard.vue';
import PatientAppointmentScheduleNextSuccessScreen from '@/Components/Patient/Appointments/PatientAppointmentScheduleNextSuccessScreen.vue';
import MobileShellPageWizard from '@/Components/shell/MobileShellPageWizard.vue';
import MobileShellWizardScrollBody from '@/Components/shell/MobileShellWizardScrollBody.vue';
import { Button } from '@/Components/ui/button';
import PatientLayout from '@/Layouts/PatientLayout.vue';
import {
    mobileShellFormWizardFooterCancelButtonClass,
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellFormWizardFooterRowClass,
    mobileShellPageFillClass,
    mobileShellWizardFormClass,
    mobileShellWizardStepPanelClass,
} from '@/lib/shell/mobileShellDialogLayout';
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

        <div :class="mobileShellPageFillClass">
            <MobileShellPageWizard :title="props.title">
                <form
                    :id="props.formId"
                    :class="mobileShellWizardFormClass"
                    novalidate
                    @submit.prevent="emit('submit')"
                >
                    <MobileShellWizardScrollBody :active="true">
                        <div :class="mobileShellWizardStepPanelClass">
                            <PatientAppointmentOutcomeSummaryCard
                                :appointment="props.appointment"
                            />

                            <slot />
                        </div>

                        <template #footer>
                            <div :class="mobileShellFormWizardFooterRowClass">
                                <Button
                                    type="submit"
                                    variant="default"
                                    size="lg"
                                    :class="
                                        mobileShellFormWizardFooterPrimaryButtonClass
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
                                        mobileShellFormWizardFooterCancelButtonClass
                                    "
                                    :disabled="props.processing"
                                    @click="
                                        router.get(
                                            route('patient.appointments'),
                                        )
                                    "
                                >
                                    {{
                                        t('patient.appointments.actions.cancel')
                                    }}
                                </Button>
                            </div>
                        </template>
                    </MobileShellWizardScrollBody>
                </form>
            </MobileShellPageWizard>
        </div>
    </PatientLayout>
</template>
