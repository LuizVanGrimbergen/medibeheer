<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
import {
    mobileShellFormWizardFooterCancelButtonClass,
    mobileShellFormWizardFooterPrimaryButtonClass,
    mobileShellFormWizardFooterRowClass,
} from '@/lib/shell/mobileShellDialogLayout';
import type { PrescriptionFormWizardStep } from '@/lib/patient/prescriptions/prescriptionFormWizardTypes';

const props = defineProps<{
    currentStep: PrescriptionFormWizardStep;
    processing: boolean;
}>();

const emit = defineEmits<{
    cancel: [];
    back: [];
}>();

const { t } = useI18n();

const backButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const primaryLabel = computed(() => {
    if (props.currentStep === 3) {
        return t('patient.prescriptions.actions.save');
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
    <div :class="mobileShellFormWizardFooterRowClass">
        <Button
            v-if="currentStep === 1"
            type="button"
            variant="secondary"
            size="lg"
            :disabled="processing"
            :class="mobileShellFormWizardFooterCancelButtonClass"
            @click.stop.prevent="handleSecondaryClick"
        >
            {{ t('patient.medications.actions.cancel') }}
        </Button>

        <Button
            v-else
            type="button"
            variant="outline"
            size="lg"
            :disabled="processing"
            :class="backButtonClass"
            @click.stop.prevent="handleSecondaryClick"
        >
            {{ t('patient.medications.actions.back') }}
        </Button>

        <Button
            type="submit"
            variant="default"
            size="lg"
            :disabled="processing"
            :class="mobileShellFormWizardFooterPrimaryButtonClass"
        >
            {{ primaryLabel }}
        </Button>
    </div>
</template>
