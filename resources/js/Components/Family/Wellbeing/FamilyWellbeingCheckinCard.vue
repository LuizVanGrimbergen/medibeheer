<script setup lang="ts">
import { Frown, Meh, Smile } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Card, CardContent } from '@/Components/ui/card';
import type {
    DailyCheckin,
    DailyCheckinSymptomValue,
    DailyMoodScoreValue,
} from '@/lib/types';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        checkin: DailyCheckin;
        density?: 'default' | 'compact';
    }>(),
    {
        density: 'default',
    },
);

const { t, locale } = useI18n();

const formattedDate = computed((): string => {
    const [y, m, d] = props.checkin.checkin_date.split('-').map(Number);

    if (!y || !m || !d) {
        return props.checkin.checkin_date;
    }

    const date = new Date(y, m - 1, d);

    return new Intl.DateTimeFormat(
        locale.value === 'nl' ? 'nl-NL' : undefined,
        {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        },
    ).format(date);
});

type MoodPresentation = {
    icon: typeof Frown;
    faceClass: string;
    iconBackgroundClass: string;
    labelKey: string;
};

const moodPresentation = computed((): MoodPresentation => {
    const map: Record<DailyMoodScoreValue, MoodPresentation> = {
        bad: {
            icon: Frown,
            faceClass: 'text-danger',
            iconBackgroundClass: 'bg-danger/10',
            labelKey: 'family.wellbeing.mood.bad',
        },
        ok: {
            icon: Meh,
            faceClass: 'text-warning',
            iconBackgroundClass: 'bg-warning/10',
            labelKey: 'family.wellbeing.mood.ok',
        },
        good: {
            icon: Smile,
            faceClass: 'text-success',
            iconBackgroundClass: 'bg-success/10',
            labelKey: 'family.wellbeing.mood.good',
        },
    };

    return map[props.checkin.mood_score];
});

const hasSymptoms = computed(
    (): boolean =>
        props.checkin.symptoms !== null && props.checkin.symptoms.length > 0,
);

function symptomLabel(symptom: DailyCheckinSymptomValue): string {
    return t(`patient.dashboard.dailyCheckins.symptoms.options.${symptom}`);
}
</script>

<template>
    <Card
        class="border-border bg-surface text-text w-full min-w-0 rounded-2xl border shadow-sm"
    >
        <CardContent
            :class="
                cn(
                    'flex flex-col',
                    props.density === 'compact'
                        ? 'gap-3 p-4'
                        : 'gap-4 p-5 sm:gap-5 sm:p-6',
                )
            "
        >
            <div class="flex min-w-0 items-start gap-4">
                <div
                    :class="
                        cn(
                            'flex size-12 shrink-0 items-center justify-center rounded-2xl sm:size-14',
                            moodPresentation.iconBackgroundClass,
                        )
                    "
                >
                    <component
                        :is="moodPresentation.icon"
                        class="size-6 shrink-0 stroke-[1.75] sm:size-7"
                        :class="moodPresentation.faceClass"
                        aria-hidden="true"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <h4
                        class="text-text-heading text-lg leading-snug font-bold wrap-break-word"
                    >
                        {{ t(moodPresentation.labelKey) }}
                    </h4>
                    <p
                        v-if="props.density === 'default'"
                        class="text-text-muted mt-1 text-sm font-medium capitalize"
                    >
                        {{ formattedDate }}
                    </p>
                    <div
                        v-if="hasSymptoms"
                        class="mt-1.5 flex flex-wrap gap-1.5"
                    >
                        <span
                            v-for="symptom in checkin.symptoms"
                            :key="symptom"
                            class="bg-surface-hover text-text-heading rounded-full px-2.5 py-0.5 text-xs font-medium"
                        >
                            {{ symptomLabel(symptom) }}
                        </span>
                    </div>
                </div>
            </div>

            <div
                v-if="checkin.note"
                class="border-border/70 bg-bg flex min-w-0 flex-col gap-1 rounded-xl border px-3 py-2.5"
            >
                <span class="text-text-muted text-xs font-semibold">
                    {{ t('family.wellbeing.noteLabel') }}
                </span>
                <p
                    class="text-text-heading min-w-0 text-sm leading-relaxed wrap-break-word whitespace-pre-wrap"
                >
                    {{ checkin.note }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
