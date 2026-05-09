<script setup lang="ts">
import { useElementSize } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DailyCheckinMoodStep from '@/Components/Patient/DailyCheckins/steps/DailyCheckinMoodStep.vue';
import DailyCheckinNoteStep from '@/Components/Patient/DailyCheckins/steps/DailyCheckinNoteStep.vue';
import DailyCheckinSymptomsStep from '@/Components/Patient/DailyCheckins/steps/DailyCheckinSymptomsStep.vue';
import { useDailyCheckin } from '@/Components/Patient/DailyCheckins/useDailyCheckin';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { useTailwindBreakpoints } from '@/composables/useTailwindBreakpoints';
import type { DailyCheckin, DailyMoodScoreValue } from '@/lib/types';

const props = defineProps<{
    today_date: string;
    today_checkin: DailyCheckin | null;
}>();

const { t } = useI18n();
const { smAndUp } = useTailwindBreakpoints();

type CheckinStep = 'mood' | 'symptoms' | 'note';

const step = ref<CheckinStep>('mood');
const showActionBar = computed(() => step.value === 'symptoms' || step.value === 'note');
const previousMood = ref<DailyMoodScoreValue | null>(null);
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
    symptomChipClass,
    toggleSymptom,
    submit,
} = useDailyCheckin(props.today_date, props.today_checkin);

function startCheckin(mood: DailyMoodScoreValue | null): void {
    if (mood === null) {
        return;
    }

    const priorMood = selectedMood.value;

    previousMood.value = priorMood;
    setMood(mood);

    if (mood === 'good') {
        resetSymptoms();
        resetNote();
        step.value = 'note';

        return;
    }

    if (priorMood !== mood) {
        resetSymptoms();
    }

    step.value = 'symptoms';
}

function backToMoodStep(): void {
    step.value = 'mood';
}

function openNoteStep(): void {
    form.symptoms = [...selectedSymptoms.value];
    resetNote();
    step.value = 'note';
}

function backFromNote(): void {
    const mood = selectedMood.value;

    if (mood === 'bad' || mood === 'ok') {
        step.value = 'symptoms';

        return;
    }

    step.value = 'mood';
}

function submitCheckin(): void {
    submit(() => {
        step.value = 'mood';
    });
}

const actionBarRef = ref<HTMLElement | null>(null);
const { height: actionBarHeight } = useElementSize(actionBarRef);

const contentPaddingBottomStyle = computed(() => {
    if (!showActionBar.value) {
        return undefined;
    }

    const heightPx = Math.max(0, actionBarHeight.value);
    const gapPx = smAndUp.value ? 8 : 12;

    return {
        paddingBottom: `${heightPx + gapPx}px`,
    };
});

watch(
    () => step.value,
    async (current) => {
        if (current !== 'note') {
            return;
        }

        await focusNoteTextarea();
    },
);
</script>

<template>
    <Card
        v-if="!today_checkin"
        class="rounded-2xl border border-border/80 bg-surface text-text shadow-md shadow-black/[0.04] sm:rounded-3xl"
    >
        <CardContent class="p-0">
            <div
                class="space-y-4 px-4 py-4 sm:space-y-6 sm:p-6 lg:p-7"
                :style="contentPaddingBottomStyle"
            >
                <DailyCheckinMoodStep
                    v-if="step === 'mood'"
                    :model-value="selectedMood"
                    :disabled="form.processing"
                    @update:model-value="startCheckin"
                />

                <DailyCheckinSymptomsStep
                    v-if="step === 'symptoms'"
                    :processing="form.processing"
                    :chip-class="symptomChipClass"
                    @toggle="toggleSymptom"
                />

                <DailyCheckinNoteStep
                    v-if="step === 'note'"
                    v-model="note"
                    :textarea-id="textareaId"
                />
            </div>

            <div
                v-if="showActionBar"
                ref="actionBarRef"
                class="fixed inset-x-0 bottom-[calc(5.5rem+env(safe-area-inset-bottom,0))] z-40 bg-transparent"
            >
                <div
                    class="mx-auto w-full max-w-7xl px-3 py-2 pb-[calc(0.5rem+env(safe-area-inset-bottom,0))] sm:px-6 sm:py-4 sm:pb-[calc(1rem+env(safe-area-inset-bottom,0))]"
                >
                    <div class="flex flex-row gap-2 sm:gap-3 sm:justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            size="lg"
                            class="min-h-11 flex-1 touch-manipulation border-2 border-danger/50 px-2.5 text-sm font-semibold text-danger hover:border-danger hover:bg-danger/10 hover:text-danger sm:min-h-14 sm:w-auto sm:min-w-40 sm:flex-initial sm:px-4 sm:text-lg"
                            :disabled="form.processing"
                            @click="step === 'symptoms' ? backToMoodStep() : backFromNote()"
                        >
                            {{
                                step === 'symptoms'
                                    ? t('patient.dashboard.dailyCheckins.symptoms.cancel')
                                    : t('patient.dashboard.dailyCheckins.noteDialog.cancel')
                            }}
                        </Button>

                        <Button
                            v-if="step === 'symptoms'"
                            type="button"
                            size="lg"
                            class="min-h-11 flex-1 touch-manipulation px-2.5 text-sm font-semibold sm:min-h-14 sm:w-auto sm:min-w-40 sm:flex-initial sm:px-4 sm:text-lg"
                            :disabled="form.processing"
                            @click="openNoteStep"
                        >
                            {{ t('patient.dashboard.dailyCheckins.symptoms.continue') }}
                        </Button>

                        <Button
                            v-else
                            type="button"
                            size="lg"
                            class="min-h-11 flex-1 touch-manipulation px-2.5 text-sm font-semibold sm:min-h-14 sm:w-auto sm:min-w-40 sm:flex-initial sm:px-4 sm:text-lg"
                            :disabled="submitDisabled"
                            @click="submitCheckin"
                        >
                            {{ t('patient.dashboard.dailyCheckins.noteDialog.confirm') }}
                        </Button>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
