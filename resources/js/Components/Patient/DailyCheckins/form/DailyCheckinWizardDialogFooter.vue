<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { DailyCheckinWizardDialogStep } from '@/Components/Patient/DailyCheckins/form/DailyCheckinWizardDialogTypes';
import PatientFormWizardFooter from '@/Components/Patient/form/PatientFormWizardFooter.vue';
import PatientFormWizardFooterButton from '@/Components/Patient/form/PatientFormWizardFooterButton.vue';
import { Button } from '@/Components/ui/button';
import {
    patientFormWizardFooterCancelButtonClass,
    patientFormWizardFooterOutlineButtonClass,
} from '@/lib/patient/patientShellDialogLayout';

const props = defineProps<{
    currentStep: DailyCheckinWizardDialogStep;
    isFirstDialogStep: boolean;
    processing: boolean;
    submitDisabled: boolean;
}>();

const emit = defineEmits<{
    cancel: [];
    back: [];
    next: [];
    submit: [];
}>();

const { t } = useI18n();

const primaryLabel = computed(() => {
    if (props.currentStep === 'note') {
        return t('patient.dashboard.dailyCheckins.noteDialog.confirm');
    }

    return t('patient.dashboard.dailyCheckins.symptoms.continue');
});
</script>

<template>
    <PatientFormWizardFooter>
        <Button
            v-if="props.isFirstDialogStep"
            type="button"
            variant="secondary"
            size="lg"
            :disabled="props.processing"
            :class="patientFormWizardFooterCancelButtonClass"
            @click="emit('cancel')"
        >
            {{
                props.currentStep === 'symptoms'
                    ? t('patient.dashboard.dailyCheckins.symptoms.cancel')
                    : t('patient.dashboard.dailyCheckins.noteDialog.cancel')
            }}
        </Button>

        <Button
            v-else
            type="button"
            variant="outline"
            size="lg"
            :disabled="props.processing"
            :class="patientFormWizardFooterOutlineButtonClass"
            @click="emit('back')"
        >
            {{ t('patient.dashboard.dailyCheckins.back') }}
        </Button>

        <PatientFormWizardFooterButton
            v-if="props.currentStep === 'note'"
            variant="primary"
            type="submit"
            :disabled="props.processing || props.submitDisabled"
        >
            {{ primaryLabel }}
        </PatientFormWizardFooterButton>

        <PatientFormWizardFooterButton
            v-else
            variant="primary"
            type="button"
            :disabled="props.processing"
            @click="emit('next')"
        >
            {{ primaryLabel }}
        </PatientFormWizardFooterButton>
    </PatientFormWizardFooter>
</template>
