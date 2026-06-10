<script setup lang="ts">
import { computed, ref, toRef } from 'vue';
import { useI18n } from 'vue-i18n';
import DailyCheckinWizardDialogFooter from '@/Components/Patient/DailyCheckins/form/DailyCheckinWizardDialogFooter.vue';
import type { DailyCheckinWizardDialogStep } from '@/Components/Patient/DailyCheckins/form/DailyCheckinWizardDialogTypes';
import { DAILY_CHECKIN_WIZARD_DIALOG_STEP_TOTAL } from '@/Components/Patient/DailyCheckins/form/DailyCheckinWizardDialogTypes';
import DailyCheckinNoteStep from '@/Components/Patient/DailyCheckins/steps/DailyCheckinNoteStep.vue';
import DailyCheckinSymptomsStep from '@/Components/Patient/DailyCheckins/steps/DailyCheckinSymptomsStep.vue';
import MobileShellFormDialog from '@/Components/shell/MobileShellFormDialog.vue';
import MobileShellWizardCard from '@/Components/shell/MobileShellWizardCard.vue';
import { DialogDescription } from '@/Components/ui/dialog';
import { usePatientFormWizardStepMotion } from '@/composables/motion/usePatientFormWizardStepMotion';
import {
    mobileShellDialogContentClass,
    mobileShellDialogDescriptionClass,
    mobileShellWizardStepPanelClass,
} from '@/lib/shell/mobileShellDialogLayout';
import type { DailyCheckinSymptomValue, DailyMoodScoreValue } from '@/lib/types';

const props = defineProps<{
    open: boolean;
    mood: DailyMoodScoreValue | null;
    currentStep: DailyCheckinWizardDialogStep;
    processing: boolean;
    submitDisabled: boolean;
    note: string;
    textareaId: string;
    chipClass: (symptom: DailyCheckinSymptomValue) => string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:currentStep': [step: DailyCheckinWizardDialogStep];
    'update:note': [value: string];
    cancel: [];
    back: [];
    next: [];
    submit: [];
    toggleSymptom: [symptom: DailyCheckinSymptomValue];
}>();

const { t } = useI18n();

const dialogContentClass = mobileShellDialogContentClass('md');
const progressLabelRef = ref<HTMLElement | null>(null);

const dialogStepNumber = computed(() =>
    props.currentStep === 'symptoms' ? 1 : 2,
);

const showWizardProgress = computed(
    () => props.mood !== null && props.mood !== 'good',
);

const progressLabel = computed(() =>
    t('patient.dashboard.dailyCheckins.stepsProgress', {
        current: dialogStepNumber.value,
        total: DAILY_CHECKIN_WIZARD_DIALOG_STEP_TOTAL,
    }),
);

const isFirstDialogStep = computed(
    () =>
        props.mood === 'good' ||
        props.mood === null ||
        props.currentStep === 'symptoms',
);

const dialogStepIndex = computed(() =>
    props.currentStep === 'symptoms' ? 0 : 1,
);

const isOpen = toRef(() => props.open);

const { wizardStepPanelRef } = usePatientFormWizardStepMotion(
    dialogStepIndex,
    isOpen,
    { progressLabelRef },
);

const noteModel = computed({
    get: () => props.note,
    set: (value: string) => emit('update:note', value),
});
</script>

<template>
    <MobileShellFormDialog
        :open="props.open"
        :title="t('patient.dashboard.dailyCheckins.title')"
        form-id="patient-daily-checkin-wizard-form"
        :dialog-content-class="dialogContentClass"
        :step-key="props.currentStep"
        @update:open="emit('update:open', $event)"
        @submit="emit('submit')"
        @cancel="emit('cancel')"
    >
        <template v-if="showWizardProgress" #description>
            <DialogDescription
                ref="progressLabelRef"
                :class="mobileShellDialogDescriptionClass"
                aria-live="polite"
            >
                {{ progressLabel }}
            </DialogDescription>
        </template>

        <div ref="wizardStepPanelRef" :class="mobileShellWizardStepPanelClass">
            <MobileShellWizardCard v-if="props.currentStep === 'symptoms'">
                <DailyCheckinSymptomsStep
                    :processing="props.processing"
                    :chip-class="props.chipClass"
                    @toggle="emit('toggleSymptom', $event)"
                />
            </MobileShellWizardCard>

            <MobileShellWizardCard v-if="props.currentStep === 'note'">
                <DailyCheckinNoteStep
                    v-model="noteModel"
                    :textarea-id="props.textareaId"
                />
            </MobileShellWizardCard>
        </div>

        <template #footer>
            <DailyCheckinWizardDialogFooter
                :current-step="props.currentStep"
                :is-first-dialog-step="isFirstDialogStep"
                :processing="props.processing"
                :submit-disabled="props.submitDisabled"
                @cancel="emit('cancel')"
                @back="emit('back')"
                @next="emit('next')"
                @submit="emit('submit')"
            />
        </template>
    </MobileShellFormDialog>
</template>
