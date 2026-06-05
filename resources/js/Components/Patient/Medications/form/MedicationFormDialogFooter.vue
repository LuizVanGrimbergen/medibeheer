<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientFormWizardFooter from '@/Components/Patient/form/PatientFormWizardFooter.vue';
import PatientFormWizardFooterButton from '@/Components/Patient/form/PatientFormWizardFooterButton.vue';
import type { MedicationFormWizardStep } from '@/Components/Patient/Medications/form/MedicationFormTypes';
import { Button } from '@/Components/ui/button';

const props = defineProps<{
    currentStep: MedicationFormWizardStep;
    processing: boolean;
}>();

const emit = defineEmits<{
    cancel: [];
    back: [];
}>();

const { t } = useI18n();

const medicationFormDialogFooterBackButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const primaryLabel = computed(() => {
    if (props.currentStep === 7) {
        return t('patient.medications.actions.save');
    }

    return t('patient.medications.actions.next');
});

function handleSecondaryClick(): void {
    if (props.currentStep === 1) {
        emit('cancel');

        return;
    }

    emit('back');
}
</script>

<template>
    <PatientFormWizardFooter>
        <PatientFormWizardFooterButton
            variant="primary"
            type="submit"
            :disabled="props.processing"
        >
            {{ primaryLabel }}
        </PatientFormWizardFooterButton>

        <PatientFormWizardFooterButton
            v-if="props.currentStep === 1"
            variant="danger"
            :disabled="props.processing"
            @click.stop.prevent="handleSecondaryClick"
        >
            {{ t('patient.medications.actions.cancel') }}
        </PatientFormWizardFooterButton>

        <Button
            v-else
            type="button"
            variant="outline"
            size="lg"
            :disabled="props.processing"
            :class="medicationFormDialogFooterBackButtonClass"
            @click.stop.prevent="handleSecondaryClick"
        >
            {{ t('patient.medications.actions.back') }}
        </Button>
    </PatientFormWizardFooter>
</template>
