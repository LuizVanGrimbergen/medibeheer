<script setup lang="ts">
import { Frown, Meh, Smile } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, CardContent, CardHeader } from '@/Components/ui/card';
import type { DailyCheckin, DailyCheckinSymptomValue, DailyMoodScoreValue } from '@/lib/types';

const props = defineProps<{
    checkin: DailyCheckin;
}>();

const { t, locale } = useI18n();

const formattedDate = computed((): string => {
    const [y, m, d] = props.checkin.checkin_date.split('-').map(Number);

    if (! y || ! m || ! d) {
        return props.checkin.checkin_date;
    }

    const date = new Date(y, m - 1, d);

    return new Intl.DateTimeFormat(locale.value === 'nl' ? 'nl-NL' : undefined, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);
});

type MoodPresentation = {
    icon: typeof Frown;
    faceClass: string;
    labelKey: string;
};

const moodPresentation = computed((): MoodPresentation => {
    const map: Record<DailyMoodScoreValue, MoodPresentation> = {
        bad: {
            icon: Frown,
            faceClass: 'text-danger',
            labelKey: 'family.wellbeing.mood.bad',
        },
        ok: {
            icon: Meh,
            faceClass: 'text-warning',
            labelKey: 'family.wellbeing.mood.ok',
        },
        good: {
            icon: Smile,
            faceClass: 'text-success',
            labelKey: 'family.wellbeing.mood.good',
        },
    };

    return map[props.checkin.mood_score];
});

function symptomLabel(symptom: DailyCheckinSymptomValue): string {
    return t(`patient.dashboard.dailyCheckins.symptoms.options.${symptom}`);
}
</script>

<template>
    <Card class="border-border">
        <CardHeader class="space-y-1 pb-2">
            <p class="text-xs font-medium uppercase tracking-wide text-text-muted">
                {{ formattedDate }}
            </p>
            <div class="flex items-center gap-2">
                <component
                    :is="moodPresentation.icon"
                    class="size-7 shrink-0 stroke-[1.75]"
                    :class="moodPresentation.faceClass"
                    aria-hidden="true"
                />
                <span class="text-base font-semibold text-text-heading">
                    {{ t(moodPresentation.labelKey) }}
                </span>
            </div>
        </CardHeader>
        <CardContent class="space-y-3 pt-0">
            <div
                v-if="checkin.symptoms !== null && checkin.symptoms.length > 0"
                class="flex flex-wrap gap-1.5"
            >
                <span
                    v-for="symptom in checkin.symptoms"
                    :key="symptom"
                    class="rounded-full bg-surface-hover px-2.5 py-0.5 text-xs font-medium text-text-heading"
                >
                    {{ symptomLabel(symptom) }}
                </span>
            </div>
            <p
                v-if="checkin.note"
                class="text-sm leading-relaxed text-text-muted"
            >
                {{ checkin.note }}
            </p>
        </CardContent>
    </Card>
</template>
