<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { DailyCheckin, DailyCheckinSymptomValue } from '@/lib/types';

const props = defineProps<{
    checkin: DailyCheckin;
}>();

const { t } = useI18n();

const hasSymptoms = computed(
    (): boolean =>
        props.checkin.symptoms !== null && props.checkin.symptoms.length > 0,
);

const hasNote = computed((): boolean => {
    const note = props.checkin.note?.trim();

    return note !== undefined && note.length > 0;
});

function symptomLabel(symptom: DailyCheckinSymptomValue): string {
    return t(`patient.dashboard.dailyCheckins.symptoms.options.${symptom}`);
}
</script>

<template>
    <div class="space-y-4">
        <section v-if="hasSymptoms" class="space-y-2.5">
            <h3 class="text-text-heading text-sm font-semibold">
                {{ t('family.wellbeing.detailsSymptomsLabel') }}
            </h3>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="symptom in checkin.symptoms"
                    :key="symptom"
                    class="border-border text-text-heading inline-flex items-center rounded-full border bg-white px-2.5 py-1 text-sm leading-snug font-semibold"
                >
                    {{ symptomLabel(symptom) }}
                </span>
            </div>
        </section>

        <section v-if="hasNote" class="space-y-2.5">
            <h3 class="text-text-heading text-sm font-semibold">
                {{ t('family.wellbeing.noteLabel') }}
            </h3>
            <p
                class="border-border/70 bg-bg text-text min-w-0 rounded-xl border px-3.5 py-3 text-base leading-relaxed wrap-break-word whitespace-pre-wrap"
            >
                {{ checkin.note }}
            </p>
        </section>
    </div>
</template>
