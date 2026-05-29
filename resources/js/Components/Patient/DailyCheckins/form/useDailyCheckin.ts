import { useForm } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';
import type {
    DailyCheckin,
    DailyCheckinSymptomValue,
    DailyMoodScoreValue,
} from '@/lib/types';
import { cn } from '@/lib/utils';

function normalizeField(value: string): string | null {
    const trimmed = value.trim();

    return trimmed === '' ? null : trimmed;
}

export function useDailyCheckin(todayDate: string, todayCheckin: DailyCheckin | null) {
    const selectedSymptoms = ref<DailyCheckinSymptomValue[]>([]);
    const selectedSymptomsSet = computed(
        () => new Set<DailyCheckinSymptomValue>(selectedSymptoms.value),
    );

    const note = ref('');
    const noteTextareaRef = ref<HTMLTextAreaElement | null>(null);

    const selectedMood = ref<DailyMoodScoreValue | null>(
        todayCheckin?.mood_score ?? null,
    );

    const form = useForm<{
        mood_score: DailyMoodScoreValue | null;
        note: string | null;
        symptoms: DailyCheckinSymptomValue[];
    }>({
        mood_score: selectedMood.value,
        note: null,
        symptoms: [],
    });

    const submitDisabled = computed(
        () => form.processing || selectedMood.value === null,
    );

    const textareaId = computed(() => `daily-checkin-note-${todayDate}`);

    function toggleSymptom(symptom: DailyCheckinSymptomValue): void {
        const on = selectedSymptomsSet.value.has(symptom);

        if (on) {
            selectedSymptoms.value = selectedSymptoms.value.filter((s) => s !== symptom);

            return;
        }

        if (selectedSymptoms.value.length >= 10) {
            return;
        }

        selectedSymptoms.value = [...selectedSymptoms.value, symptom];
    }

    function symptomChipClass(symptom: DailyCheckinSymptomValue): string {
        const on = selectedSymptomsSet.value.has(symptom);

        return cn(
            'daily-checkin-symptom-chip touch-manipulation border-2 !text-base !leading-snug transition-colors sm:!text-lg',
            on
                ? 'border-primary bg-primary/12 text-text-heading'
                : 'border-border bg-surface text-text-heading hover:bg-surface-hover',
        );
    }

    function resetSymptoms(): void {
        selectedSymptoms.value = [];
        form.symptoms = [];
    }

    function resetNote(): void {
        note.value = '';
        form.note = null;
    }

    function setMood(mood: DailyMoodScoreValue): void {
        selectedMood.value = mood;
        form.mood_score = mood;
    }

    function submit(onSuccess: () => void): void {
        form.note = normalizeField(note.value);
        form.symptoms =
            selectedMood.value === 'good' ? [] : [...selectedSymptoms.value];

        form.post(route('patient.daily-checkins.store'), {
            preserveScroll: true,
            onSuccess: () => {
                resetNote();
                onSuccess();
            },
        });
    }

    async function focusNoteTextarea(): Promise<void> {
        await nextTick();
        noteTextareaRef.value?.focus({ preventScroll: true });
    }

    return {
        form,
        note,
        noteTextareaRef,
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
    };
}

