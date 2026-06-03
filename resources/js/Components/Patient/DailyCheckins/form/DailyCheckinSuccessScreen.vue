<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import PatientActionSuccessScreen from '@/Components/Patient/PatientActionSuccessScreen.vue';
import type { DailyMoodScoreValue } from '@/lib/types';

const props = defineProps<{
    mood: DailyMoodScoreValue | null;
    message?: string | null;
}>();

const { t } = useI18n();
const dismissed = ref(false);

watch(
    () => props.mood,
    () => {
        dismissed.value = false;
    },
);

const open = computed({
    get: () => props.mood !== null && !dismissed.value,
    set: (value: boolean) => {
        if (!value) {
            dismissed.value = true;
        }
    },
});

const fallbackMessageKey = computed(() =>
    props.mood === 'good'
        ? 'patient.dashboard.dailyCheckins.success.messageGood'
        : 'patient.dashboard.dailyCheckins.success.messageComfort',
);

const bodyMessage = computed(() => {
    if (typeof props.message === 'string' && props.message.trim() !== '') {
        return props.message;
    }

    return t(fallbackMessageKey.value);
});

function onDone(): void {
    dismissed.value = true;
}
</script>

<template>
    <PatientActionSuccessScreen
        v-model:open="open"
        :title="t('patient.dashboard.dailyCheckins.success.title')"
        :message="bodyMessage"
        :done-label="t('patient.dashboard.dailyCheckins.success.done')"
        @done="onDone"
    />
</template>
