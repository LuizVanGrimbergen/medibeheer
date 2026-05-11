<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import DailyCheckinMoodStep from '@/Components/Patient/DailyCheckins/steps/DailyCheckinMoodStep.vue';
import DailyCheckinNoteStep from '@/Components/Patient/DailyCheckins/steps/DailyCheckinNoteStep.vue';
import DailyCheckinSymptomsStep from '@/Components/Patient/DailyCheckins/steps/DailyCheckinSymptomsStep.vue';
import { useDailyCheckin } from '@/Components/Patient/DailyCheckins/useDailyCheckin';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    patientAppointmentFormPrimaryPairButtonClass,
    patientSoftDangerActionButtonClass,
} from '@/lib/patient/appointments/patientSoftDangerActionButtonClass';
import type { DailyCheckin, DailyMoodScoreValue } from '@/lib/types';

const props = defineProps<{
    today_date: string;
    today_checkin: DailyCheckin | null;
}>();

const { t } = useI18n();

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
    <div v-if="!today_checkin" class="space-y-3 sm:space-y-4">
        <Card
            class="rounded-2xl border border-border/80 bg-surface text-text shadow-md shadow-black/[0.04] sm:rounded-3xl"
        >
            <CardContent class="p-0">
                <div
                    class="space-y-5 rounded-2xl bg-surface px-4 py-4 sm:space-y-6 sm:rounded-3xl sm:px-5 sm:py-5 md:p-7 lg:p-8"
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
            </CardContent>
        </Card>

        <Card
            v-if="showActionBar"
            class="rounded-2xl border border-border/80 bg-transparent text-text shadow-sm shadow-black/[0.03] sm:rounded-3xl"
        >
            <CardContent class="px-4 py-3 sm:px-5 sm:py-4 md:px-7 lg:px-8">
                <div
                    class="flex min-w-0 w-full flex-col-reverse gap-2 sm:flex-row sm:gap-3"
                >
                    <Button
                        type="button"
                        variant="secondary"
                        size="lg"
                        :class="patientSoftDangerActionButtonClass"
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
                        variant="default"
                        size="lg"
                        :class="patientAppointmentFormPrimaryPairButtonClass"
                        :disabled="form.processing"
                        @click="openNoteStep"
                    >
                        {{ t('patient.dashboard.dailyCheckins.symptoms.continue') }}
                    </Button>

                    <Button
                        v-else
                        type="button"
                        variant="default"
                        size="lg"
                        :class="patientAppointmentFormPrimaryPairButtonClass"
                        :disabled="submitDisabled"
                        @click="submitCheckin"
                    >
                        {{ t('patient.dashboard.dailyCheckins.noteDialog.confirm') }}
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
