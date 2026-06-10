<script setup lang="ts">
import { ref, watch } from 'vue';
import DailyCheckinWizardDialog from '@/Components/Patient/DailyCheckins/form/DailyCheckinWizardDialog.vue';
import type { DailyCheckinWizardDialogStep } from '@/Components/Patient/DailyCheckins/form/DailyCheckinWizardDialogTypes';
import { useDailyCheckin } from '@/Components/Patient/DailyCheckins/form/useDailyCheckin';
import DailyCheckinMoodStep from '@/Components/Patient/DailyCheckins/steps/DailyCheckinMoodStep.vue';
import MobileShellWizardCard from '@/Components/shell/MobileShellWizardCard.vue';
import type { DailyCheckin, DailyMoodScoreValue } from '@/lib/types';

const props = defineProps<{
    today_date: string;
    today_checkin: DailyCheckin | null;
}>();

const dialogOpen = ref(false);
const dialogStep = ref<DailyCheckinWizardDialogStep>('symptoms');
const dialogMood = ref<DailyMoodScoreValue | null>(null);

const {
    form,
    note,
    selectedMood,
    selectedSymptoms,
    submitDisabled,
    textareaId,
    focusNoteTextarea,
    resetNote,
    resetSymptoms,
    setMood,
    clearMood,
    symptomChipClass,
    toggleSymptom,
    submit,
} = useDailyCheckin(props.today_date, props.today_checkin);

function resetDialogState(): void {
    dialogStep.value = 'symptoms';
    resetSymptoms();
    resetNote();
    clearMood();
    dialogMood.value = null;
}

function startCheckin(mood: DailyMoodScoreValue | null): void {
    if (mood === null) {
        return;
    }

    const moodChanged = selectedMood.value !== mood;

    setMood(mood);
    dialogMood.value = mood;

    if (mood === 'good') {
        resetSymptoms();
        resetNote();
        dialogStep.value = 'note';
    } else {
        if (moodChanged) {
            resetSymptoms();
        }

        dialogStep.value = 'symptoms';
    }

    dialogOpen.value = true;
}

function handleDialogCancel(): void {
    dialogOpen.value = false;
}

function handleDialogBack(): void {
    if (dialogStep.value === 'note' && dialogMood.value !== 'good') {
        dialogStep.value = 'symptoms';

        return;
    }

    handleDialogCancel();
}

function openNoteStep(): void {
    form.symptoms = [...selectedSymptoms.value];
    resetNote();
    dialogStep.value = 'note';
}

function submitCheckin(): void {
    submit(() => {
        dialogOpen.value = false;
    });
}

function handleDialogOpenChange(open: boolean): void {
    dialogOpen.value = open;

    if (open) {
        return;
    }

    globalThis.setTimeout(() => {
        resetDialogState();
    }, 200);
}

watch(
    () => dialogStep.value,
    async (current) => {
        if (!dialogOpen.value || current !== 'note') {
            return;
        }

        await focusNoteTextarea();
    },
);
</script>

<template>
    <div class="space-y-3 sm:space-y-4">
        <MobileShellWizardCard v-if="!dialogOpen">
            <DailyCheckinMoodStep
                :model-value="selectedMood"
                :disabled="form.processing"
                @update:model-value="startCheckin"
            />
        </MobileShellWizardCard>

        <DailyCheckinWizardDialog
            :open="dialogOpen"
            :mood="dialogMood"
            v-model:current-step="dialogStep"
            v-model:note="note"
            :processing="form.processing"
            :submit-disabled="submitDisabled"
            :textarea-id="textareaId"
            :chip-class="symptomChipClass"
            @update:open="handleDialogOpenChange"
            @cancel="handleDialogCancel"
            @back="handleDialogBack"
            @next="openNoteStep"
            @toggle-symptom="toggleSymptom"
            @submit="submitCheckin"
        />
    </div>
</template>
