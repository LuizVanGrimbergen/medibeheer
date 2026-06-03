<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/Components/ui/button';
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

const primaryButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation gap-2.5 rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const cancelButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl border-2 border-danger/40 bg-danger/10 px-3 text-base font-semibold text-danger hover:border-danger hover:bg-danger/20 hover:text-danger md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const backButtonClass =
    'min-h-12 min-w-0 w-full touch-manipulation rounded-2xl px-3 text-base font-semibold md:min-h-14 md:flex-1 md:px-4 lg:text-lg';

const primaryLabel = computed(() => {
    if (props.currentStep === 2) {
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
    <div
        class="flex w-full min-w-0 flex-col gap-2 md:flex-row-reverse md:gap-3"
    >
        <Button
            type="submit"
            variant="default"
            size="lg"
            :disabled="processing"
            :class="primaryButtonClass"
        >
            {{ primaryLabel }}
        </Button>

        <Button
            v-if="currentStep === 1"
            type="button"
            variant="secondary"
            size="lg"
            :disabled="processing"
            :class="cancelButtonClass"
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
    </div>
</template>
